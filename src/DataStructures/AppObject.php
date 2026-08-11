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
 * @extends ImmutableArray<non-decimal-int-string, array<non-decimal-int-string, mixed>>
 */
final readonly class AppObject extends ImmutableArray
{
    /**
     * @param array<string, mixed> $array
     */
    public function __construct(array $array)
    {
        if (array_is_list($array) && [] !== $array) {
            throw new InvalidArgumentException('App array must be an associative array with string keys, not a list.');
        }
        foreach ($array as $key => $value) {
            if (!is_string($key)) {
                throw new InvalidArgumentException("Property keys of AppObjects MUST be strings, which is not the case of the key '{$key}'.");
            }
        }

        parent::__construct($array);
    }

    public function map(callable $callback): static
    {
        return new self(array_map($callback, $this->data));
    }

    /**
     * @param non-decimal-int-string $key The key of the property.
     * @return bool Whether the AppObject instance has the specified property.
     */
    public function hasProperty(string $key): bool
    {
        return $this->offsetExists($key);
    }

    /**
     * Create a new AppObject with the specified property removed.
     *
     * @param non-decimal-int-string $keyToRemove The key of the property to remove.
     * @return AppObject Another AppObject with the same data as this one, but
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
     * @param mixed $value The new value of the specified property.
     * @return AppObject An identical AppObject with the requested change executed.
     */
    public function set(string $offset, mixed $value): self
    {
        return new self([$offset => $value] + $this->data);
    }

    /**
     * @todo Could return true even if two objects are not of the same class but
     * both inherit from AppObject.
     * 
     * @param self $value
     */
    public function isEqual(self $value): bool
    {
        if (count($value) !== count($this)) {
            return false;
        }

        foreach ($value as $key => $item) {
            if (!$this->offsetExists($key)) {
                return false;
            }
            if ($item instanceof self && !$item->isEqual($this->getAppObject($key))) {
                return false;
            } elseif ($this->data[$key] !== $item) {
                return false;
            }
        }

        return true;
    }
}
