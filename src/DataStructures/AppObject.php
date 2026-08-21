<?php

declare(strict_types=1);

namespace LMWF\DataStructures;

use InvalidArgumentException;
use OutOfBoundsException;

/**
 * Immutable array consisting of key-value pairs named properties. Keys are
 * necessarily string, and values can be any data type.
 *
 * @todo Force a certain naming style for property keys?
 *
 * @template TValue
 * @extends ImmutableArray<non-decimal-int-string, TValue, array<string, TValue>>
 */
final readonly class AppObject extends ImmutableArray
{
    /**
     * @param array<string, TValue> $array
     */
    public function __construct(array $array = [])
    {
        if (array_is_list($array) && [] !== $array) {
            throw new InvalidArgumentException('App array must be an associative array with string keys, not a list.');
        }
        foreach ($array as $key => $_) {
            if (!is_string($key)) {
                throw new InvalidArgumentException("Property keys of AppObjects MUST be strings, which is not the case of the key '{$key}'.");
            }
        }

        parent::__construct($array);
    }

    /**
     * @template TReturn of mixed
     * @param callable(TValue): TReturn $callback
     * @return self<TReturn>
     */
    public function map(callable $callback): self
    {
        new self(array_map($callback, $this->data));
        return new self(array_map($callback, $this->data));
    }

    /**
     * Create a new AppObject with the specified property removed.
     *
     * @param non-decimal-int-string $keyToRemove The key of the property to remove.
     * @return self<TValue> Another AppObject with the same data as this one, but
     * with the specified key removed.
     */
    public function removeProperty(string $keyToRemove): self
    {
        if (!$this->offsetExists($keyToRemove)) {
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
     * @param non-decimal-int-string $offset The key of the property to set.
     * @param TValue $value The new value of the specified property.
     * @return self<TValue> An identical AppObject with the requested change executed.
     */
    public function set(string $offset, mixed $value): self
    {
        return new self([$offset => $value] + $this->data);
    }

    /**
     * @todo Could return true even if two objects are not of the same class but
     * both inherit from AppObject.
     * @todo Do we need this method?
     *
     * @param self<mixed> $appObject
     */
    public function isEqual(self $appObject): bool
    {
        if (count($appObject) !== count($this)) {
            return false;
        }

        foreach ($appObject as $pName => $value) {
            if (!$this->offsetExists($pName)) {
                return false;
            }
            if ($value instanceof self && !$value->isEqual($this->getAppObject($pName))) {
                return false;
            } elseif ($this->data[$pName] !== $value) {
                return false;
            }
        }

        return true;
    }
}
