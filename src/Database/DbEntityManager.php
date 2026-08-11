<?php

declare(strict_types=1);

namespace LM\WebFramework\Database;

use DateMalformedStringException;
use DateTimeImmutable;
use InvalidArgumentException;
use LM\WebFramework\Database\Exceptions\InvalidDbDataException;
use LM\WebFramework\Database\Exceptions\NullDbDataNotAllowedException;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\Model\Type\AbstractEntityModel;
use LM\WebFramework\Model\Type\BoolModel;
use LM\WebFramework\Model\Type\DateTimeModel;
use LM\WebFramework\Model\Type\EntityModel;
use LM\WebFramework\Model\Type\ForeignEntityModel;
use LM\WebFramework\Model\Type\IntModel;
use LM\WebFramework\Model\Type\IScalarModel;
use LM\WebFramework\Model\Type\EntityListModel;
use LM\WebFramework\Model\Type\ListModel;
use LM\WebFramework\Model\Type\StringModel;
use UnexpectedValueException;

/**
 * @todo Could be renamed to DbEntityFactory / DbArrayFactory.
 */
final class DbEntityManager
{
    const SEP = '_';

    private function isOrdered(array $array): bool {
        return count($array) === count(array_filter($array, fn($key) => is_int($key), ARRAY_FILTER_USE_KEY));
    }

    /**
     * Transform DB Data into App Data.
     * 
     * The following order of priority applies when converting the DB data into
     * app data: bool, int, DateTime, and finally string.
     *
     * @param int|string|null $dbData DB Data.
     * @param IScalarModel $model The model of the DB Data.
     * @return mixed A PHP scalar type or base class object.
     * @throws InvalidArgumentException If $dbData is not of any DB Data variable type.
     */
    public function convertDbScalar(bool|int|string|null $dbData, IScalarModel $model): mixed
    {
        if ($model instanceof BoolModel && (0 === $dbData || 1 === $dbData)) {
            return 1 === $dbData;
        } elseif ($model instanceof DateTimeModel && is_string($dbData)) {
            try {
                return new DateTimeImmutable($dbData);
            } catch (DateMalformedStringException) {
            }
        } elseif ($model instanceof IntModel && is_numeric($dbData)) {
            return intval($dbData);
        } elseif ($model instanceof StringModel && is_string($dbData)) {
            return $dbData;
        } elseif ($model->isNullable() && is_null($dbData)) {
            return null;
        } else {
            if (null === $dbData) {
                throw new NullDbDataNotAllowedException($dbData, $model);
            } else {
                throw new InvalidDbDataException($dbData, $model);
            }
        }
    }

    /**
     * @param array[] $dbRows A list of associative arrays each storing a
     * different row.
     * @param AbstractEntityModel $model The model of each row.
     * @param int $index The row identifier of the main entity.
     */
    public function convertDbRowsToAppObject(array $dbRows, AbstractEntityModel $model, int $index = 0): AppObject
    {
        if (!array_is_list($dbRows)) {
            throw new InvalidArgumentException('$dbRows must be a list of rows.');
        }

        $transientAppObject = [];
        foreach ($model->getProperties() as $key => $property) {
            $value = null;

            if ($property instanceof ForeignEntityModel) {
                $parentId = $dbRows[$index][$model->getIdentifier() . self::SEP . $property->getParentIdKey()];
                $linkedRowsKeys = array_filter(
                    array_keys($dbRows),
                    function ($rowKey) use ($dbRows, $parentId, $property) {
                        return $dbRows[$rowKey][$property->getIdentifier() . self::SEP . $property->getChildIdKey()] === $parentId;
                    },
                );
                if (count($linkedRowsKeys) > 0) {
                    $value = $this->convertDbRowsToAppObject($dbRows, $property, $linkedRowsKeys[0]);
                }
            } elseif ($property instanceof EntityModel) {
                $value = $this->convertDbRowsToAppObject($dbRows, $property, $index);
            } elseif ($property instanceof EntityListModel) {
                $itemModel = $property->getItemModel();
                $parentId = $dbRows[$index][$itemModel->getIdentifier() . self::SEP . $itemModel->getParentIdKey()];
                $value = $this->convertDbEntityList($dbRows, $property, $parentId);
            } else {
                $value = $this->convertDbScalar($dbRows[$index][$model->getIdentifier() . self::SEP . $key], $property);
            }

            $transientAppObject[$key] = $value;
        }
        return new AppObject($transientAppObject);
    }

    public function convertDbEntityList(array $dbRows, EntityListModel $EntityListModel, ?string $parentId) : array
    {
        $itemModel = $EntityListModel->getItemModel();
        $appItems = [];
        $ids = [];
        
        foreach ($dbRows as $rowIndex => $row) {
            $rowId = $row[$itemModel->getIdentifier() . self::SEP . $itemModel->getChildIdKey()];
            if ((null === $parentId || $rowId === $parentId) && !in_array($rowId, $ids)) {
                $appItems[] = $this->convertDbRowsToAppObject($dbRows, $itemModel, $rowIndex);
            }
        }

        return $appItems;
    }

    public function convertDbList(array $dbRows, ListModel $listModel): array
    {
        $itemModel = $listModel->getItemModel();
        $appData = [];
        foreach ($dbRows as $key => $row) {
            if ($itemModel instanceof IScalarModel) {
                $appData[] = $this->convertDbScalar($row, $itemModel);
            } elseif ($itemModel instanceof AbstractEntityModel) {
                $appData[] = $this->convertDbRowsToAppObject($dbRows, $itemModel, $key);
            } elseif ($itemModel instanceof ListModel) {
                $appData[] = $this->convertDbList($row, $itemModel);
            }
        }
        return $appData;
    }

    /**
     * Ignores list (ordered arrays).
     *
     * @throws UnexpectedValueException If some of the properties are set to be persisted and are not scalar.
     * @throws InvalidArgumentException If appData is a list.
     */
    public function toDbValue(mixed $appData, string $prefix = ''): mixed {
        if ($appData instanceof AppObject) {
            return $this->toDbValue($appData->toArray(), $prefix);
        } elseif (is_bool($appData)) {
            return $appData ? 1 : 0;
        } elseif ($appData instanceof DateTimeImmutable) {
            return $appData->format('Y-m-d H:i:s');
        } elseif (is_array($appData)) {
            $dbArray = [];
            if ($this->isOrdered($appData)) {
                throw new InvalidArgumentException('Not supported.');
            } else {
                foreach ($appData as $pName => $pValue) {
                    if (is_array($pValue)) {
                        if (!$this->isOrdered($pValue)) {
                            $dbArray += $this->toDbValue($pValue, $pName);
                        }
                    } else {
                        $dbArray[$prefix . $pName] = $this->toDbValue($pValue);
                    }
                }
            }
            return $dbArray;
        } else {
            return $appData;
        }
    }
}