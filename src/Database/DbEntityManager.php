<?php

declare(strict_types=1);

namespace LM\WebFramework\Database;

use DateMalformedStringException;
use DateTimeImmutable;
use InvalidArgumentException;
use LM\WebFramework\Database\Exceptions\InvalidDbDataException;
use LM\WebFramework\Database\Exceptions\NullDbDataNotAllowedException;
use LM\WebFramework\DataStructures\AppList;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\DataStructures\Factory\CollectionFactory;
use LM\WebFramework\Model\Type\AbstractEntityModel;
use LM\WebFramework\Model\Type\BoolModel;
use LM\WebFramework\Model\Type\DateTimeModel;
use LM\WebFramework\Model\Type\EntityModel;
use LM\WebFramework\Model\Type\ForeignEntityModel;
use LM\WebFramework\Model\Type\IntModel;
use LM\WebFramework\Model\Type\IScalarModel;
use LM\WebFramework\Model\Type\EntityListModel;
use LM\WebFramework\Model\Type\IModel;
use LM\WebFramework\Model\Type\ListModel;
use LM\WebFramework\Model\Type\StringModel;
use LM\WebFramework\Validation\Validator;
use UnexpectedValueException;

/**
 * @todo Could be renamed to DbEntityFactory / DbArrayFactory.
 */
final class DbEntityManager
{
    public const SEP = '_';

    /**
     * @todo Could be removed? (And array_is_list could be used instead.)
     */
    private function isOrdered(array $array): bool
    {
        return count($array) === count(array_filter($array, fn ($key) => is_int($key), ARRAY_FILTER_USE_KEY));
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
     * @todo Create type for dbRows, as a list of associative arrays?
     * @todo Throw exception is passed array is empty.
     * @param array[] $dbRows A list of associative arrays each storing a
     * different row.
     * @param AbstractEntityModel $model The model of each row.
     * @param int $index The row identifier of the main entity.
     */
    public function convertDbRowsToAppObject(array $dbRows, EntityModel $model, int $index = 0): AppObject
    {
        if (!array_is_list($dbRows)) {
            throw new InvalidArgumentException('$dbRows must be a list of rows.');
        }

        $transientAppObject = [];

        foreach ($model->getProperties() as $key => $property) {
            $value = null;


            if ($property instanceof ForeignEntityModel) {
                /**
                 * @todo If $property is nullable, don’t throw exception!
                 * @todo Create method to get referenceId.
                 * @todo Should return null?
                 */
                $referenceId = $dbRows[$index][$model->getIdentifier() . self::SEP . $property->getReferenceKeyInParent()];
                if (null !== $referenceId) {
                    $referencedRowNos = $this->getReferencedRowNos($dbRows, $property, $referenceId);
                    if (count($referencedRowNos) > 0) {
                        $value = $this->convertDbRowsToAppObject($dbRows, $property->getEntityModel(), $referencedRowNos[0]);
                    } elseif (!$property->isNullable()) {
                        throw new UnexpectedValueException("Could not find specifed foreign entity using reference ID {$referenceId} and reference key {$property->getReferenceKeyInParent()} in parent and {$property->getReferencedKeyInChild()} in child for property {$key}.");
                    }
                } elseif (!$property->isNullable()) {
                    // @todo Add test for this edge case.
                    throw new InvalidArgumentException('Mandatory sub entity reference id is null.');
                }

            } elseif ($property instanceof EntityModel) {
                $value = $this->convertDbRowsToAppObject($dbRows, $property, $index);
            } elseif ($property instanceof EntityListModel) {
                $itemModel = $property->getItemModel();
                $referenceId = $dbRows[$index][$model->getIdentifier() . self::SEP . $itemModel->getReferenceKeyInParent()];
                $value = $this->convertDbEntityList($dbRows, $property, $referenceId);
            } else {
                $value = $this->convertDbScalar($dbRows[$index][$model->getIdentifier() . self::SEP . $key], $property);
            }

            $transientAppObject[$key] = $value;
        }

        return (new CollectionFactory())->createDeepAppObject($transientAppObject);
    }

    public function convertDbEntityList(array $dbRows, EntityListModel $entityListModel, int|string|null $referenceId): array
    {
        $itemModel = $entityListModel->getItemModel();
        $appItems = [];
        $ids = [];

        foreach ($dbRows as $rowIndex => $row) {
            $rowReferenceId = $row[$itemModel->getEntityModel()->getIdentifier() . self::SEP . $itemModel->getReferencedKeyInChild()];
            $rowId = $row[$itemModel->getEntityModel()->getIdentifier() . self::SEP . $itemModel->getEntityModel()->getIdKey()];
            if ((null === $referenceId || $rowReferenceId === $referenceId) && !in_array($rowId, $ids)) {
                $appItems[] = $this->convertDbRowsToAppObject($dbRows, $itemModel->getEntityModel(), $rowIndex);
                $ids[] = $rowId;
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
            } elseif ($itemModel instanceof EntityModel) {
                $appData[] = $this->convertDbRowsToAppObject($dbRows, $itemModel, $key);
            } elseif ($itemModel instanceof ForeignEntityModel) {
                $appData[] = $this->convertDbRowsToAppObject($dbRows, $itemModel->getEntityModel(), $key);
            } elseif ($itemModel instanceof ListModel) {
                $appData[] = $this->convertDbList($row, $itemModel);
            }
        }
        return $appData;
    }

    public function convertDbRowsToList(array $dbRows, IModel $itemModel): AppList
    {
        if ($itemModel instanceof EntityModel) {
            return $this->convertDbRowsToEntityList($dbRows, $itemModel);
        }
        $appData = [];
        foreach ($dbRows as $rowNo => $row) {
            if ($itemModel instanceof IScalarModel) {
                $appData[] = $this->convertDbScalar($row, $itemModel);
            } elseif ($itemModel instanceof EntityModel) {
                $appData[] = $this->convertDbRowsToAppObject($dbRows, $itemModel, $rowNo);
            } elseif ($itemModel instanceof ForeignEntityModel) {
                $appData[] = $this->convertDbRowsToAppObject($dbRows, $itemModel->getEntityModel(), $rowNo);
            } elseif ($itemModel instanceof ListModel) {
                $appData[] = $this->convertDbList($row, $itemModel);
            }
        }
        return new AppList($appData);
    }

    public function convertDbRowsToEntityList(array $dbRows, EntityModel $itemModel): AppList
    {
        $appData = [];
        $ids = [];
        foreach ($dbRows as $rowNo => $row) {
            $rowEntityId = $row[$itemModel->getIdentifier() . self::SEP . $itemModel->getIdKey()];
            if (!in_array($rowEntityId, $ids, strict: true)) {
                $appData[] = $this->convertDbRowsToAppObject($dbRows, $itemModel, $rowNo);
                $ids[] = $rowEntityId;
            }
        }
        return new AppList($appData);
    }

    /**
     * Verifies and filter out any extra property from an AppObject.
     *
     * @param AppObject $appObject The AppObject instance to check and prune.
     * @param EntityModel $model The model that the AppObject should adhere to.
     * @return AppObject A verified AppObject trimmed of any extra property.
     * @todo Good location for this method?
     */
    public function pruneAppObject(AppObject $appObject, EntityModel $model): AppObject
    {
        $cvs = (new Validator($model))->validate($appObject->toArray());
        if (count($cvs) > 0) {
            throw new InvalidArgumentException('Given app object does not adhere to the given model.');
        }
        $data = [];
        foreach ($model->getProperties() as $key => $property) {
            if ($property instanceof ForeignEntityModel && null !== $appObject[$key]) {
                $data[$key] = $this->pruneAppObject($appObject[$key], $property->getEntityModel());
            } else {
                $data[$key] = $appObject[$key];
            }
        }
        return (new CollectionFactory())->createDeepAppObject($data);
    }

    /**
     * Ignores list (ordered arrays).
     *
     * @throws UnexpectedValueException If some of the properties are set to be persisted and are not scalar.
     * @throws InvalidArgumentException If appData is a list.
     */
    public function toDbValue(mixed $appData, string $prefix = '', array $ignoreProperties = []): mixed
    {
        if ($appData instanceof AppObject) {
            return $this->toDbValue($appData->toArray(), $prefix, $ignoreProperties);
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
                    if (!in_array($pName, $ignoreProperties, true)) {
                        if (is_array($pValue)) {
                            if (!$this->isOrdered($pValue)) {
                                $dbArray += $this->toDbValue($pValue, $pName);
                            }
                        } else {
                            $dbArray[$prefix . $pName] = $this->toDbValue($pValue);
                        }
                    }
                }
            }
            return $dbArray;
        } else {
            return $appData;
        }
    }

    /**
     * Perform an outer join on two result sets, as if the two were issued from
     * an outer join request.
     *
     * @param array[] $dbRowsLeft A list of rows returned from the database.
     * @param array[] $dbRowsRight A list of rows returned from the database.
     * @return array[] A list of rows with $dbRowsRight appended to $dbRowsLeft,
     * and with each row having all the same columns.
     */
    public function outerJoinDbRows(array $dbRowsLeft, array $dbRowsRight): array
    {
        if (0 === count($dbRowsLeft)) {
            return $dbRowsRight;
        } elseif (0 === count($dbRowsRight)) {
            return $dbRowsLeft;
        }

        $emptyRow = array_map(fn () => null, $dbRowsLeft[0] + $dbRowsRight[0]);

        $dbRows = [];

        for ($i = 0; $i < max(count($dbRowsLeft), count($dbRowsRight)); $i++) {
            if ($i < count($dbRowsLeft)) {
                $dbRows[$i] = $dbRowsLeft[$i] + ($dbRowsRight[$i] ?? $emptyRow);
            } else {
                $dbRows[$i] = array_merge($emptyRow, $dbRowsRight[$i]);
            }
        }

        return $dbRows;
    }

    private function getReferencedRowNos(
        array $dbRows,
        ForeignEntityModel $property,
        int|string $referenceId,
    ): array {
        $prunedDbRows = array_filter(
            $dbRows,
            fn ($row) => $row[$property->getEntityModel()->getIdentifier() . self::SEP . $property->getReferencedKeyInChild()] === $referenceId,
        );

        return array_keys($prunedDbRows);
    }
}
