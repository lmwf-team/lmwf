<?php

declare(strict_types=1);

namespace LM\WebFramework\Database;

use DateTimeImmutable;
use InvalidArgumentException;
use LM\WebFramework\Database\Exceptions\InvalidDbDataException;
use LM\WebFramework\Database\Exceptions\NullDbDataNotAllowedException;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\Model\IEntity;
use LM\WebFramework\Model\IForeignEntity;
use LM\WebFramework\Model\IList;
use LM\WebFramework\Model\IModel;
use LM\WebFramework\Model\IScalar;
use UnexpectedValueException;

/**
 * @todo Could be renamed to DbEntityFactory / DbArrayFactory.
 */
class DbEntityManager
{
    const SEP = '_';

    private function isOrdered(array $array): bool {
        return count($array) === count(array_filter($array, fn($key) => is_int($key), ARRAY_FILTER_USE_KEY));
    }

    public function convertDbDataRow(array $dbRows, IModel $model, string $prefix, int $index = 0): AppObject
    {
        if (null !== $model->getListNodeModel()) {
            return $this->convertDbDataToList($dbRows, $model);
        }
        if (null !== $model->getArrayDefinition()) {
            $transientAppData = [];
            foreach ($model->getArrayDefinition() as $key => $property) {
                if (null !== $property->getArrayDefinition() || null !== $property->getListNodeModel()) {
                    $transientAppData[$key] = $this->convertDbDataRow($dbRows, $property, $index);
                } else {
                    $transientAppData[$key] = $this->convertDbDataValue($dbRows[$index][$prefix . self::SEP . $key], $property);
                }
            }
            return new AppObject($transientAppData);
        }

        throw new InvalidDbDataException('Model must have an array definition.', $model);
    }

    /**
     * Transform DB Data into App Data.
     * 
     * The following order of priority applies when converting the DB data into
     * app data: bool, int, DateTime, and finally string.
     *
     * @param int|string|null $dbData DB Data.
     * @param IScalar $model The model of the DB Data.
     * @return mixed A PHP scalar type or base class object.
     * @throws InvalidArgumentException If $dbData is not of any DB Data variable type.
     */
    public function convertDbScalar(bool|int|string|null $dbData, IScalar $model): mixed
    {
        if (is_numeric($dbData)) {
            if ($model->isBool() && in_array($dbData, [0, 1], true)) {
                return 1 === $dbData;
            } elseif (null !== $model->getIntegerConstraints()) {
                return intval($dbData);
            }
        }
        if (is_string($dbData)) {
            if (null !== $model->getDateTimeConstraints()) {
                return new DateTimeImmutable($dbData);
            }
            if (null !== $model->getStringConstraints()) {
                return $dbData;
            }
        }
        if (null === $dbData) {
            if ($model->isNullable()) {
                return null;
            }
            throw new NullDbDataNotAllowedException($dbData, $model);
        }

        throw new InvalidDbDataException($dbData, $model);
    }

    public function convertDbDataToList(array $dbList, IEntity|IModel $nodeModel, string $prefix): array
    {
        if (is_array($dbList) && array_is_list($dbList)) {
            if ($nodeModel instanceof IEntity) {

                return array_map(
                    function ($i) use ($dbList, $nodeModel, $prefix) {
                        return $this->convertDbDataRow($dbList, $nodeModel, $prefix, $i);
                    },
                    array_keys($dbList),
                );
            } elseif ($nodeModel instanceof IList) {
                throw new InvalidArgumentException('Nested lists not supported yet.');
            } else {
                return array_map(
                    function ($node) use ($nodeModel) {
                        return $this->convertDbDataValue($node, $nodeModel);
                    },
                    $dbList,
                );
            }
            
        }

        throw new InvalidDbDataException('Given $dbList must be a list.', $model);
    }

    /**
     * @param array[] $dbRows A list of rows each stored as associative arrays.
     * @param IEntity $entity The model of each row.
     * @param int $index The row identifier of the main entity.
     */
    public function convertDbRowsToAppObject(array $dbRows, IEntity $entity, int $index = 0): AppObject
    {
        if (array_is_list($dbRows)) {
            throw new InvalidArgumentException('$dbRows must be a list of rows.');
        }

        $transientAppObject = [];
        foreach ($entity->getProperties() as $key => $property) {
            $value = null;
            if ($property instanceof IForeignEntity) {
                $linkedRowsKeys = array_filter(
                    array_keys($dbRows),
                    function ($rowKey) use ($dbRows, $index, $property) {
                        return $property->isLinked($dbRows[$index], $dbRows[$rowKey]);
                    },
                );
                if (count($linkedRowsKeys) > 0) {
                    $value = $this->convertDbRowsToAppObject($dbRows, $property, $linkedRowsKeys[0]);
                }
            } else {
                $value = $this->convertDbDataToValue($dbRows[$index][$key], $property);
            }
            $transientAppObject[$key] = $value;
        }
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

    /**
     * @param $dbRows array A list of database rows mapped by column.
     */
    private function convertListToAppObject(array $dbRows, IModel $model, ?string $prefix = null, int $index = 0): ?AppObject
    {
        if (!array_is_list($dbRows)) {
            throw new InvalidArgumentException('Expected list argument.');
        }

        if (null !== $listNodeModel = $model->getListNodeModel()) {
            // Make convertListToAppObject return a list of whatever the list node model is.
            $appDataList = [];
            // To prevent adding the same item twice
            $ids = [];
            
            // For each list property, we will check each row of $dbRows
            foreach ($dbRows as $rowIndex => $row) {
                if (null !== $row["{$prefix}_id"] & !in_array($row["{$prefix}_id"], $ids, true)) {
                    $appDataList[] = $this->convertListToAppObject($dbRows, $listNodeModel, $prefix, $rowIndex);
                    $ids[] = $row["{$prefix}_id"];
                }
            }
            return new AppObject($appDataList);
        }
        elseif (null !== $arrayModel = $model->getArrayDefinition()) {
            $transientAppObject = [];
            foreach ($arrayModel as $pKey => $pModel) {
                if (null !== $pModel->getArrayDefinition() || null !== $pModel->getListNodeModel()) {
                    $subPrefix = 's' === substr($pKey, strlen($pKey) - 1) ? $subPrefix = substr($pKey, 0, strlen($pKey) - 1) : $pKey;
                    $parentEntityId = $dbRows[$index][$prefix . self::SEP . 'id'];
                    $relatedDbRows = array_values(array_filter($dbRows, function ($row) use ($prefix, $parentEntityId){
                        return $row[$prefix . self::SEP . 'id'] === $parentEntityId;
                    }));
                    $transientAppObject[$pKey] = $this->convertListToAppObject($relatedDbRows, $pModel, $subPrefix, $index);
                } else {
                    $transientAppObject[$pKey] = $this->toAppData($dbRows[$index][$prefix . self::SEP . $pKey], $pModel);
                }
            }

            return new AppObject($transientAppObject);
        }
        else {
            throw new InvalidArgumentException('$dbRows is not valid.');
        }
    }

    private function convertNonListArrayToAppObject(array $dbArray, IModel $model, ?string $prefix = null): ?AppObject
    {
        if (null === $model->getArrayDefinition()) {
            throw new InvalidDbDataException($dbArray, $model, $prefix);
        }

        $appArray = [];
        $nValidNull = 0;
        $nInvalidNull = 0;
        $firstNullException = null;
        foreach ($model->getArrayDefinition() as $key => $property) {
            try {
                if (null !== $property->getArrayDefinition()) {
                    $appData = $this->toAppData($dbArray, $property, $key);
                } elseif (null !== $property->getListNodeModel()) {
                    $subPrefix = 's' === substr($key, strlen($key) - 1) ? $subPrefix = substr($key, 0, strlen($key) - 1) : $key;
                    $appData = $this->toAppData($dbArray[$key], $property, $subPrefix);
                } else {
                    $appData = $this->toAppData(
                        $dbArray[$prefix . self::SEP . $key],
                        $property,
                    );
                }
            } catch (NullDbDataNotAllowedException $e) {
                $appData = $e;
                $nInvalidNull++;
                if (null === $firstNullException) {
                    $firstNullException = $e;
                }
            }
            if (null === $appData) {
                $nValidNull++;
            }
            $appArray[$key] = $appData;
        }

        if (null !== $firstNullException) {
            if (count($appArray) == $nValidNull + $nInvalidNull) {
                return $this->toAppData(null, $model, $prefix);
            }
            throw $firstNullException;
        }
        
        return new AppObject($appArray);
    }
}