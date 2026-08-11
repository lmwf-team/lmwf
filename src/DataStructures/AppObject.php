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
 * Immutable array whose values can be accessed as properties.
 */
final class AppObject implements ArrayAccess, Countable, IteratorAggregate
{
    private array $data;

    /**
     * @param array<string, array|bool|int|string|null> $appArray An app array.
     */
    public function __construct(array $appArray)
    {
        if (array_is_list($appArray)) {
            throw new InvalidArgumentException('App array must be an associative array with string keys, not a list.');
        }
        $this->data = [];
        foreach ($appArray as $key => $value) {
            $this->data[$key] = $this->toAppObject($value);
        }
            
    }

    private function toAppObject(mixed $appValue): mixed
    {
        if ($appValue instanceof AppObject) {
            return $appValue;
        } elseif (is_array($appValue)) {
            if (array_is_list($appValue)) {
                $list = [];
                foreach ($appValue as $value) {
                    $list[] = $this->toAppObject($value);
                }
                return $list;
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
     * @return Another Another AppObject with the same data as this one, but
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

    public function set(string $offet, mixed $value): self
    {
        return new self([$offet => $value] + $this->data);
    }

    public function toArray(): array
    {
        $appArray = [];
        foreach ($this->data as $pName => $pValue) {
            $appArray[$pName] = $pValue instanceof self ? $pValue->toArray() : $pValue;
        }
        return $appArray;
    }

    public function isEqualTo(mixed $appObject): bool
    {
        if (!($appObject instanceof AppObject)) {
            return false;
        }
        foreach ($this->data as $key => $value) {
            $isEqual = null;
            if ($value instanceof AppObject) {
                $isEqual = $value->isEqualTo($appObject[$key]);
            } elseif (gettype($value) === 'object') {
                $isEqual = $value == $appObject[$key];
            } else {
                $isEqual = $value === $appObject[$key];
            }
            if (!$isEqual) {
                return false;
            }
        }
        return true;
    }
}