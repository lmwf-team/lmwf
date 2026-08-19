<?php

declare(strict_types=1);

namespace LMWF\DataStructures;

use InvalidArgumentException;

/**
 * Immutable list of heterogeneous data, guaranteed to have zero-indexed
 * sequential property keys.
 *
 * @template TValue = mixed
 * @extends ImmutableArray<int, TValue, list<TValue>>
 */
final readonly class AppList extends ImmutableArray
{
    /**
     * @param list<TValue> $data
     */
    public function __construct(array $data = [])
    {
        if (!array_is_list($data)) {
            throw new InvalidArgumentException('Constructor must receive a list.');
        }

        parent::__construct($data);
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
     * @param callable(TValue): bool $callback
     * @return self<TValue>
     */
    public function filter(callable $callback): self
    {
        return new self(array_values(array_filter($this->data, $callback)));
    }
}
