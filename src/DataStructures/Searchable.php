<?php

declare(strict_types=1);

namespace LM\WebFramework\DataStructures;

use InvalidArgumentException;

final class Searchable
{
    public function __construct(
        private string $name,
        private float $importance,
    ) {
        if ($importance < 0 || $importance > 1) {
            throw new InvalidArgumentException();
        }
    }

    public function getName(): string {
        return $this->name;
    }

    public function getImportance(): float {
        return $this->importance;
    }
}