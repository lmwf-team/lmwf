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

    private function convertListToAppObject(array $dbList, IModel $model, ?string $prefix = null): ?AppObject
    {
        if (!array_is_list($dbList)) {
            throw new InvalidArgumentException('Given data MUST be a list.');
        }

        if (null !== $model->getListNodeModel()) {
            $appArray = [];
            foreach ($dbList as $row) {
                $appArray[] = $this->toAppData($row, $model->getListNodeModel(), $prefix);
            }
            return new AppObject($appArray);
        } elseif (null !== $model->getArrayDefinition()) {

            // 1. Separate, in the array definition, the list properties from the non-list properties
            $nonListProperties = [];
            $listProperties = [];
            foreach ($model->getArrayDefinition() as $key => $property) {
                if ($property->getListNodeModel()) {
                    $listProperties[$key] = $property;
                } else {
                    $nonListProperties[$key] = $property;
                }
            }

            // 2. Convert to an app object the model formed by the non-list properties
            $appObject = $this->toAppData($dbList[0], new AbstractEntity($nonListProperties), $prefix);

            // 3. Add to the app object the missing list properties
            foreach ($listProperties as $key => $property) {
                $subPrefix = 's' === substr($key, strlen($key) - 1) ? $subPrefix = substr($key, 0, strlen($key) - 1) : $key;

                // List of items matching the current property
                $items = [];
                $ids = [];
                
                // For each list property, we will check each row of $dbList
                foreach ($dbList as $row) {
                    if (null !== $row["{$subPrefix}_id"] & !in_array($row["{$subPrefix}_id"], $ids, true)) {
                        $items[] = $this->toAppData($row, $property->getListNodeModel(), $subPrefix);
                    }
                }

                $appObject = $appObject->set($key, $items);
            }

            return $appObject;
        } else {
            throw new InvalidDbDataException($dbList, $model, $prefix);
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