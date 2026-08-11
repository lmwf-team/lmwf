<?php

namespace LM\WebFramework\Database;

use DateTimeImmutable;
use InvalidArgumentException;
use LM\WebFramework\DataStructures\AppObject;
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
     * @return mixed App Data.
     * @throws InvalidArgumentException If $dbData is not of any DB Data variable type.
     */
    public function toAppData(mixed $dbData, IModel $model, ?string $prefix = null): mixed {
        if (is_array($dbData)) {
            if (null !== $model->getArrayDefinition()) {
                $appArray = [];
                foreach ($model->getArrayDefinition() as $key => $property) {
                    if (null !== $property->getArrayDefinition()) {
                        // echo "<br>";
                        // var_dump($key, get_class($property), gettype($dbData));

                        // Could be used to consider an array of null values as a null value.
                        // foreach ($property->getArrayDefinition() as $subkey => $subproperty) {
                        //     var_dump($dbData[$key . self::SEP . $subkey]);
                        // }
                        $appArray[$key] = $this->toAppData($dbData, $property, $key);
                    } elseif (null !== $property->getListNodeModel()) {
                        $subPrefix = 's' === substr($key, strlen($key) - 1) ? $subPrefix = substr($key, 0, strlen($key) - 1) : $key;

                        $appArray[$key] = $this->toAppData($dbData[$key], $property, $subPrefix);
                    } else {
                        // echo "<br>";
                        // var_dump($key, get_class($property), $prefix . self::SEP . $key, $dbData[$prefix . self::SEP . $key]);
                        $appArray[$key] = $this->toAppData(
                            $dbData[$prefix . self::SEP . $key],
                            $property,
                        );
                    }
                }
                if (count($appArray) === count(array_filter($appArray, fn ($value) => null === $value))) {
                    return null;
                } else {
                    return new AppObject($appArray);
                }
            } elseif (null !== $model->getListNodeModel()) {
                $appArray = [];
                foreach ($dbData as $row) {
                    $appArray[] = $this->toAppData($row, $model->getListNodeModel(), $prefix);
                }
                return $appArray;
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
        }

        throw new InvalidArgumentException(
            '$dbData is not of any type supported by the "' . get_class($model) . "\" with prefix \"{$prefix}\".\n" .
            var_export($dbData, true)
        );
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