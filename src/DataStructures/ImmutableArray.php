<?php

declare(strict_types=1);

namespace LMWF\DataStructures;

use ArrayAccess;
use ArrayIterator;
use BadMethodCallException;
use Countable;
use IteratorAggregate;
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
 * @template TValue of mixed
 * @template TArray of array<TKey, TValue>
 * @implements ArrayAccess<TKey, TValue>
 * @implements IArrayable<TKey, TValue>
 * @implements IteratorAggregate<TKey, TValue>
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
     * @param callable(TValue, TKey): bool $callback
     */
    public function all(callable $callback): bool
    {
        return array_all($this->data, $callback);
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
    #[\Override]
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

    #[\Override]
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
     * @return TValue
     */
    public function get(int|string $key): mixed
    {
        return $this->data[$key];
    }

    /**
     * @param TKey $key
     */
    public function getAppList(int|string $key): AppList
    {
        $value = $this->data[$key];
        if ($value instanceof AppList) {
            return $value;
        }
        throw new UnexpectedPropertyType($key, AppList::class, $value);
    }

    /**
     * @param TKey $key
     * @return AppObject<mixed>
     */
    public function getAppObject(int|string $key): AppObject
    {
        $value = $this->data[$key];
        if ($value instanceof AppObject) {
            return $value;
        } elseif ($value instanceof AppList and 0 === $value->count()) {
            return new AppObject([]);
        }
        throw new UnexpectedPropertyType($key, AppObject::class, $value);
    }

    /**
     * @template T
     * @param TKey $key
     * @param class-string<T> $itemFqcn
     * @return AppObject<T>
     */
    public function getAppObjectWithItemClass(int|string $key, string $itemFqcn): AppObject
    {
        $value = $this->data[$key];
        if ($value instanceof AppObject) {
            foreach ($value as $subkey => $item) {
                if (!$item instanceof $itemFqcn) {
                    throw new UnexpectedPropertyType("$key.$subkey", AppObject::class, $value);
                }
            }
            return $value;
        } elseif ($value instanceof AppList and 0 === $value->count()) {
            return new AppObject([]);
        }
        throw new UnexpectedPropertyType($key, AppObject::class, $value);
    }

    /**
     * @param TKey $key
     * @return mixed[]
     */
    public function getArray(int|string $key): array
    {
        $value = $this->data[$key];
        if (is_array($value)) {
            return $value;
        }
        throw new UnexpectedPropertyType($key, 'array', $value);
    }


    /**
     * @param TKey $key
     * @return IArrayable<TKey, mixed>
     */
    public function getArrayable(int|string $key): IArrayable
    {
        $value = $this->data[$key];
        if ($value instanceof IArrayable) {
            return $value;
        }
        throw new UnexpectedPropertyType($key, IArrayable::class, $value);
    }


    /**
     * @param TKey $key
     */
    public function getBool(int|string $key): bool
    {
        $value = $this->data[$key];
        if (is_bool($value)) {
            return $value;
        }
        throw new UnexpectedPropertyType($key, 'bool', $value);
    }


    /**
     * @param TKey $key
     */
    public function getFloat(int|string $key): float
    {
        $value = $this->data[$key];
        if (is_float($value)) {
            return $value;
        }
        throw new UnexpectedPropertyType($key, 'float', $value);
    }

    /**
     * @param TKey $key
     * @return int
     */
    public function getInt(int|string $key): int
    {
        $value = $this->data[$key];
        if (is_int($value)) {
            return $value;
        }
        throw new UnexpectedPropertyType($key, 'int', $value);
    }

    /**
     * @param TKey $key
     * @return positive-int
     */
    public function getIntStrictlyPositive(int|string $key): int
    {
        $value = $this->data[$key];
        if (is_int($value)) {
            if ($value < 1) {
                throw new UnexpectedValueException("Property with key '$key' has a value of '$value', which is not strictly positive.");
            }
            return $value;
        }
        throw new UnexpectedPropertyType($key, 'int', $value);
    }

    /**
     * @param TKey $key
     */
    public function getNullableObject(int|string $key, string $fqcn): mixed
    {
        $value = $this->data[$key];

        if (null === $value || (is_object($value) && get_class($value) === $fqcn)) {
            return $value;
        }

        throw new UnexpectedPropertyType($key, "?$fqcn", $value);
    }

    /**
     * @param TKey $key
     */
    public function getNullableScalar(int|string $key, string $type): mixed
    {
        $value = $this->data[$key];

        if (null === $value || gettype($value) === $type) {
            return $value;
        }

        throw new UnexpectedPropertyType($key, "?$type", $value);
    }

    /**
     * @param TKey $key
     */
    public function getString(int|string $key): string
    {
        $value = $this->data[$key];

        if (is_string($value)) {
            return $value;
        }

        throw new UnexpectedPropertyType($key, "string", $value);
    }

    /**
     * @param TKey $offset
     */
    #[\Override]
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
    #[\Override]
    public function offsetExists(mixed $offset): bool
    {
        foreach ($this->data as $key => $_) {
            if ($key === $offset) {
                return true;
            }
        }

        return false;
    }

    #[\Override]
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new BadMethodCallException(self::class . ' objects are immutable.');
    }

    #[\Override]
    public function offsetUnset(mixed $offset): void
    {
        throw new BadMethodCallException(self::class . ' objects are immutable.');
    }

    /**
     * @return TArray
     * @todo Delete?
     */
    #[\Override]
    public function toArray(): array
    {
        return $this->data;
    }
}
