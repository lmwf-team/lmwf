<?php

declare(strict_types=1);

namespace LM\WebFramework\DataStructures;

use ArrayAccess;
use ArrayIterator;
use BadMethodCallException;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use OutOfBoundsException;
use Traversable;

/**
 * Immutable array consisting of key-value pairs named properties. Keys are
 * necessarily string, and values can be any data type (except associative
 * arrays as they are turned into AppObject).
 */
final class AppObject implements ArrayAccess, Countable, IteratorAggregate
{
    /**
     * array $data The associative array storing the object’s properties.
     */
    private array $data;

    /**
     * @param array<string, mixed> $appArray An associative array.
     */
    public function __construct(array $appArray)
    {
        if (array_is_list($appArray)) {
            throw new InvalidArgumentException('App array must be an associative array with string keys, not a list.');
        }
        $this->data = [];
        foreach ($appArray as $key => $value) {
            $this->data[(string) $key] = $this->convertPropertyValue($value);
        }
            
    }

    /**
     * Convert properties in an associative array used to create an AppObject
     * instance.
     * @param mixed $appValue The value of one of the array’s properties.
     * @return mixed The converted value to use in the AppObject instance being
     * created.
     */
    private function convertPropertyValue(mixed $appValue): mixed
    {
        if ($appValue instanceof AppObject) {
            return $appValue;
        } elseif (is_array($appValue)) {
            if (array_is_list($appValue)) {
                return array_map(fn ($item) => $this->convertPropertyValue($item), $appValue);
            } else {
                return new self($appValue);
            }
        } else {
            return $appValue;
        }
    }

    public function __get(string $name): mixed
    {
        return $this->attributeGet($name);
    }

    public function attributeGet(string $offset): mixed
    {
        $keyName = (new KeyName($offset))->__toString();
        return $this->data[$keyName];
    }

    public function count(): int
    {
        return count($this->data);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
    }

    /**
     * @param string $key The key of the requested property.
     * @return AppObject The requested property value.
     */
    public function getValueAsAppObject(string $key): self
    {
        return $this->data[$key];
    }

    /**
     * @param string $key The key of the requested property.
     * @return int The requested property value.
     */
    public function getValueAsInt(string $key): int
    {
        return $this->data[$key];
    }

    /**
     * @param string $key The key of the requested property.
     * @return string The requested property value.
     */
    public function getValueAsString(string $key): string
    {
        return $this->data[$key];
    }

    /**
     * @param string $key The key of the property.
     * @return bool Whether the AppObject instance has the specified property.
     */
    public function hasProperty(string $key): bool
    {
        return key_exists($key, $this->data);
    }

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->data);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return isset($this->data[$offset]) ? $this->data[$offset] : $this->attributeGet($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new BadMethodCallException('This object cannot be modified.');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new BadMethodCallException('This object cannot be modified.');
    }

    /**
     * Create a new AppObject with the specified property removed.
     * 
     * @param string $keyToRemove The key of the property to remove.
     * @return AppObject Another AppObject with the same data as this one, but
     * with the specified key removed.
     */
    public function removeProperty(string $keyToRemove): self
    {
        if (!key_exists($keyToRemove, $this->data)) {
            throw new OutOfBoundsException("There is no property with the key: {$keyToRemove}.");
        }
        $newData = [];
        foreach ($this->data as $key => $value) {
            if ($keyToRemove !== $key) {
                $newData[$key] = $value;
            }
        }

        return new self($newData);
    }

    /**
     * @param string $offset The key of the property to set.
     * @param mixed $value The new value of the specified property.
     * @return AppObject An identical AppObject with the requested change executed.
     */
    public function set(string $offet, mixed $value): self
    {
        return new self([$offet => $value] + $this->data);
    }

    /**
     * @return array The AppObject instance’s underlying associative array, with
     * AppObject instances also turned into associative arrays.
     */
    public function toArray(): array
    {
        $appArray = [];
        foreach ($this->data as $pName => $pValue) {
            $appArray[$pName] = $pValue instanceof self ? $pValue->toArray() : $pValue;
        }
        return $appArray;
    }

    /**
     * @param mixed $mixed The value to compare the AppObject with.
     * @return bool Whether the given value is another AppObject with an
     * identical content.
     */
    public function isEqualTo(mixed $mixed): bool
    {
        if (!($mixed instanceof AppObject)) {
            return false;
        }
        foreach ($this->data as $key => $value) {
            $isEqual = null;
            if ($value instanceof AppObject) {
                $isEqual = $value->isEqualTo($mixed[$key]);
            } elseif (gettype($value) === 'object') {
                $isEqual = $value == $mixed[$key];
            } else {
                $isEqual = $value === $mixed[$key];
            }
            if (!$isEqual) {
                return false;
            }
        }
        return true;
    }
}