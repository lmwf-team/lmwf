<?php

declare(strict_types=1);

namespace LM\WebFramework\DataStructures;

use InvalidArgumentException;

/**
 * Immutable list guaranteed to have zero-indexed sequential property keys.
 *
 */
class AppList extends ImmutableArray
{
    public function __construct(array $data)
    {
        if (!array_is_list($data)) {
            throw new InvalidArgumentException('Constructor must receive a list.');
        }

        parent::__construct($data);
    }

    public function implode(string $separator): string
    {
        return implode($separator, $this->data);
    }

    public function map(callable $callback): self
    {
        return new self(array_map($callback, $this->data));
    }

    public function filter(callable $callback): self
    {
        return new self(array_values(array_filter($this->data, $callback)));
    }
}
