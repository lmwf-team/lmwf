<?php

declare(strict_types=1);

namespace LMWF\DataStructures;

use InvalidArgumentException;

/**
 * Immutable list of heterogeneous data, guaranteed to have zero-indexed
 * sequential property keys.
 *
 * @extends ImmutableArray<int, list<mixed>>
 */
final readonly class AppList extends ImmutableArray
{
    /**
     * @param list<mixed> $data
     */
    public function __construct(array $data)
    {
        if (!array_is_list($data)) {
            throw new InvalidArgumentException('Constructor must receive a list.');
        }

        parent::__construct($data);
    }

    public function map(callable $callback): static
    {
        return new self(array_map($callback, $this->data));
    }

    public function filter(callable $callback): self
    {
        return new self(array_values(array_filter($this->data, $callback)));
    }
}
