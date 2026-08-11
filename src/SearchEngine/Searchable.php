<?php

declare(strict_types=1);

namespace LMWF\SearchEngine;

use InvalidArgumentException;

final readonly class Searchable
{
    public function __construct(
        public string $name,
        public float $importance,
    ) {
        if ($importance < 0 || $importance > 1) {
            throw new InvalidArgumentException();
        }
    }
}
