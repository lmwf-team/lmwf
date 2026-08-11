<?php

declare(strict_types=1);

namespace LMWF\DataStructures;

use ArrayAccess;
use ArrayIterator;
use BadMethodCallException;
use Countable;
use IteratorAggregate;
use IteratorIterator;
use LMWF\DataStructures\Exceptions\UnexpectedPropertyType;
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
 * @template TKey of int|non-decimal-int-string
 * @template TArray of array<TKey, mixed>
 * @implements ArrayAccess<TKey, mixed>
 * @implements IArrayable<TKey, mixed>
 * @implements IteratorAggregate<TKey, mixed>
 * @phpstan-consistent-constructor
 */
abstract readonly class ImmutableArray implements ArrayAccess, Countable, IArrayable, IteratorAggregate
{
    /**
     * @param TArray $data
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
     * @return int<0, max>
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

        /**
         * @todo Open issue in phpstan.
         * @phpstan-ignore return.type
         */
        return new ArrayIterator($this->data);
    }

    /**
     * @param TKey $key
     */
    public function getAppList(int|string $key): AppList
    {
        $value = $this[$key];
        if ($value instanceof AppList) {
            return $value;
        }
        throw new UnexpectedPropertyType($key, AppList::class);
    }


    /**
     * @param TKey $key
     */
    public function getAppObject(int|string $key): AppObject
    {
        $value = $this[$key];
        if ($value instanceof AppObject) {
            return $value;
        }
        throw new UnexpectedPropertyType($key, AppObject::class);
    }


    /**
     * @param TKey $key
     * @return mixed[]
     */
    public function getArray(int|string $key): array
    {
        $value = $this[$key];
        if (is_array($value)) {
            return $value;
        }
        throw new UnexpectedPropertyType($key, 'array');
    }


    /**
     * @param TKey $key
     * @return IArrayable<TKey, mixed>
     */
    public function getArrayable(int|string $key): IArrayable
    {
        $value = $this[$key];
        if ($value instanceof IArrayable) {
            return $value;
        }
        throw new UnexpectedPropertyType($key, IArrayable::class);
    }


    /**
     * @param TKey $key
     */
    public function getBool(int|string $key): bool
    {
        $value = $this[$key];
        if (is_bool($value)) {
            return $value;
        }
        throw new UnexpectedPropertyType($key, 'bool');
    }


    /**
     * @param TKey $key
     */
    public function getFloat(int|string $key): float
    {
        $value = $this[$key];
        if (is_float($value)) {
            return $value;
        }
        throw new UnexpectedPropertyType($key, 'float');
    }


    /**
     * @param TKey $key
     */
    public function getInt(int|string $key): int
    {
        $value = $this[$key];
        if (is_int($value)) {
            return $value;
        }
        throw new UnexpectedPropertyType($key, 'int');
    }

    /**
     * @param TKey $key
     */
    public function getNullableObject(int|string $key, string $fqcn): mixed
    {
        $value = $this[$key];

        if (null === $value || (is_object($value) && get_class($value) === $fqcn)) {
            return $value;
        }

        throw new UnexpectedPropertyType($key, "?$fqcn");
    }

    /**
     * @param TKey $key
     */
    public function getNullableScalar(int|string $key, string $type): mixed
    {
        $value = $this[$key];

        if (null === $value || gettype($value) === $type) {
            return $value;
        }

        throw new UnexpectedPropertyType($key, "?$type");
    }

    /**
     * @param TKey $key
     */
    public function getString(int|string $key): string
    {
        $value = $this[$key];

        if (is_string($value)) {
            return $value;
        }
    
        throw new UnexpectedPropertyType($key, "string");
    }

    /**
     * @param callable(mixed): mixed $callback
     */
    public abstract function map(callable $callback): static;

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
        foreach ($this->data as $key => $_) {
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

    /**
     * @return array<TKey, mixed>
     * @todo Delete?
     */
    public function toArray(): array
    {
        $data = [];
        foreach ($this->data as $pName => $pValue) {
            $data[$pName] = $pValue instanceof IArrayable ? $pValue->toArray() : $pValue;
        }
        return $data;
    }
}
