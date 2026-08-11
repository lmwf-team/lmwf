<?php

namespace LM\WebFramework\Database;

use DateTimeImmutable;
use InvalidArgumentException;
use LM\WebFramework\Database\Exceptions\InvalidDbDataException;
use LM\WebFramework\Database\Exceptions\NullDbDataNotAllowedException;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\Model\AbstractEntity;
use LM\WebFramework\Model\IModel;
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

    /**
     * Transform DB Data into App Data.
     *
     * @param mixed $dbData DB Data.
     * @param IModel $model The model of the DB Data.
     * @param string|null $prefix If DB Data is an array, the prefix to use when extracting its properties.
     * @return mixed App Data. (An AppObject, a boolean, etc.)
     * @throws InvalidArgumentException If $dbData is not of any DB Data variable type.
     */
    public function toAppData(mixed $dbData, IModel $model, ?string $prefix = null): mixed {
        if (is_array($dbData)) {
            if (array_is_list($dbData)) {
                return $this->convertListToAppObject($dbData, $model, $prefix);
            }
            else {
                return $this->convertNonListArrayToAppObject($dbData, $model, $prefix);
            }
        }
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
            throw new NullDbDataNotAllowedException($dbData, $model, $prefix);
        }

        throw new InvalidDbDataException($dbData, $model, $prefix);
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