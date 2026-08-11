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
 * either integers or strings (the only admissible key types in PHP), and values
 * can be any data type.
 * 
 * @template TKey
 * @implements ArrayAccess<TKey, mixed> 
 * @implements IArrayable<TKey, mixed>
 * @implements IteratorAggregate<TKey, mixed>
 */
abstract readonly class ImmutableArray implements ArrayAccess, Countable, IArrayable, IteratorAggregate
{
    /**
     * @param array<TKey, mixed> $data
     */
    public function __construct(
        protected array $data,
    ) {
    }

    /**
     * @param mixed $value
     */
    public function contains(mixed $value): bool
    {
        foreach ($this->data as $datum) {
            if ($value === $datum) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return positive-int
     */
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * @return list<TKey>
     */
    public function keys(): array
    {
        return array_keys($this->data);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
    }

    /**
     * @param TKey $key
     */
    public function getAppList(mixed $key): AppList
    {
        return $this[$key];
    }


    /**
     * @param TKey $key
     */
    public function getAppObject(mixed $key): AppObject
    {
        return $this[$key];
    }


    /**
     * @param TKey $key
     * @return mixed[]
     */
    public function getArray(mixed $key): array
    {
        return $this[$key];
    }


    /**
     * @param TKey $key
     * @return IArrayable<TKey, mixed>
     */
    public function getArrayable(mixed $key): IArrayable
    {
        return $this[$key];
    }


    /**
     * @param TKey $key
     */
    public function getBool(mixed $key): bool
    {
        return $this[$key];
    }


    /**
     * @param TKey $key
     */
    public function getFloat(mixed $key): float
    {
        return $this[$key];
    }


    /**
     * @param TKey $key
     */
    public function getInt(mixed $key): int
    {
        return $this[$key];
    }

    /**
     * @param TKey $key
     */
    public function getNullableObject(mixed $key, string $fqcn): mixed
    {
        $value = $this[$key];

        if (null !== $value && get_class($value) !== $fqcn) {
            throw new UnexpectedValueException('Requested property value is not of the desired type.');
        }

        return $value;
    }

    /**
     * @param TKey $key
     */
    public function getNullableScalar(mixed $key, string $type): mixed
    {
        $value = $this[$key];

        if (null !== $value && gettype($value) !== $type) {
            throw new UnexpectedValueException('Requested property value is not of the desired type.');
        }

        return $value;
    }

    /**
     * @param TKey $key
     */
    public function getString(mixed $key): string
    {
        return $this[$key];
    }

    /**
     * @param TKey $offset
     */
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
     * 
     * @param TKey $offset
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


    /**
     * @param TKey $offset
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new BadMethodCallException(self::class . ' objects are immutable.');
    }


    /**
     * @param TKey $offset
     */
    public function offsetUnset(mixed $offset): void
    {
        throw new BadMethodCallException(self::class . ' objects are immutable.');
    }

    /**
     * @return array<TKey, null|object|scalar>
     */
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

        foreach ($value as $key => $item) {
            if (!$this->offsetExists($key)) {
                return false;
            }
            if ($item instanceof IDistinguishable && !$item->isEqual($this->data[$key])) {
                return false;
            } elseif ($this->data[$key] !== $item) {
                return false;
            }
        }

        return true;
    }
}
