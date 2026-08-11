<?php

declare(strict_types=1);

namespace LM\WebFramework\DataStructures;

use ArrayAccess;
use ArrayIterator;
use BadMethodCallException;
use Countable;
use IteratorAggregate;
use OutOfBoundsException;
use Traversable;
use UnexpectedValueException;

/**
 * Immutable array.
 *
 * An immutable array consists of key-value pairs named properties. Keys are
 * either integers or strings, and values can be any data type.
 */
abstract class ImmutableArray implements ArrayAccess, Countable, IArrayable, IteratorAggregate
{
    public function __construct(
        protected array $data,
    ) {
    }

    public function count(): int
    {
        return count($this->data);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
    }

    public function getAppList(mixed $key): AppList
    {
        return $this[$key];
    }

    public function getAppObject(mixed $key): AppObject
    {
        return $this[$key];
    }

    public function getArray(mixed $key): array
    {
        return $this[$key];
    }

    public function getArrayable(mixed $key): IArrayable
    {
        return $this[$key];
    }

    public function getBool(mixed $key): bool
    {
        return $this[$key];
    }

    public function getFloat(mixed $key): float
    {
        return $this[$key];
    }

    public function getInt(mixed $key): int
    {
        return $this[$key];
    }

    public function getNullableObject(mixed $key, string $fqcn): mixed
    {
        $value = $this[$key];

        if (null !== $value && get_class($value) !== $fqcn) {
            throw new UnexpectedValueException('Requested property value is not of the desired type.');
        }

        return $value;
    }

    public function getNullableScalar(mixed $key, string $type): mixed
    {
        $value = $this[$key];

        if (null !== $value && gettype($value) !== $type) {
            throw new UnexpectedValueException('Requested property value is not of the desired type.');
        }

        return $value;
    }

    public function getString(mixed $key): string
    {
        return $this[$key];
    }

    public function offsetGet(mixed $offset): mixed
    {
        foreach ($this->data as $key => $value) {
            if ($key === $offset) {
                return $value;
            }
        }

        throw new OutOfBoundsException("Object does not posess the specified property ({$offset}).");
    }

    /**
     * Built-in method that checks that given offset exists in the array and is
     * STRICTLY the same.
     *
     * For instance, an object with the property key '3' will return false if
     * given an offset of 3.
     */
    public function offsetExists(mixed $offset): bool
    {
        foreach ($this->data as $key => $_value) {
            if ($key === $offset) {
                return true;
            }
        }

        return false;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new BadMethodCallException(self::class . ' objects are immutable.');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new BadMethodCallException(self::class . ' objects are immutable.');
    }

    public function toArray(): array
    {
        $data = [];
        foreach ($this->data as $pName => $pValue) {
            $data[$pName] = $pValue instanceof IArrayable ? $pValue->toArray() : $pValue;
        }
        return $data;
    }

    /**
     * @todo Could return true even if two objects are not of the same class but
     * both inherit from ImmutableArray.
     */
    public function isEqual(mixed $value): bool
    {
        if (!($value instanceof self)) {
            return false;
        }

        if (count($value) !== count($this)) {
            return false;
        }

        foreach ($value as $key => $value) {
            if (!$this->offsetExists($key)) {
                return false;
            }
            if ($value instanceof IDistinguishable && !$value->isEqual($this->data[$key])) {
                return false;
            } elseif ($this->data[$key] !== $value) {
                return false;
            }
        }

        return true;
    }
}
