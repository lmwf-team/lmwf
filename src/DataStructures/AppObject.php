<?php

declare(strict_types=1);

namespace LM\WebFramework\DataStructures;

use InvalidArgumentException;
use OutOfBoundsException;

/**
 * Immutable array consisting of key-value pairs named properties. Keys are
 * necessarily string, and values can be any data type (except associative
 * arrays as they are turned into AppObject).
 *
 * @todo Force a certain naming style for property keys?
 */
final class AppObject extends ImmutableArray
{
    /**
     * @param array<string, mixed> $appArray An associative array.
     */
    public function __construct(array $array)
    {
        if (array_is_list($array)) {
            throw new InvalidArgumentException('App array must be an associative array with string keys, not a list.');
        }

        foreach ($array as $key => $_value) {
            if (!is_string($key)) {
                throw new InvalidArgumentException('Property keys of AppObjects MUST be strings.');
            }
        }

        parent::__construct($array);
    }

    /**
     * @param string $key The key of the property.
     * @return bool Whether the AppObject instance has the specified property.
     */
    public function hasProperty(string $key): bool
    {
        return $this->offsetExists($key);
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
     * @param string $offset The key of the property to set.
     * @param mixed $value The new value of the specified property.
     * @return AppObject An identical AppObject with the requested change executed.
     */
    public function set(string $offet, mixed $value): self
    {
        return new self([$offet => $value] + $this->data);
    }

    /**
     * @param mixed $mixed The value to compare the AppObject with.
     * @return bool Whether the given value is another AppObject with an
     * identical content.
     */
    public function isEqual(mixed $mixed): bool
    {
        if (!($mixed instanceof AppObject)) {
            return false;
        }

        return parent::isEqual($mixed);
    }
}
