<?php

declare(strict_types=1);

namespace LMWF\Form\DataStructures;

final readonly class NonExistingEnumCase
{
    public function __construct(
        public string $value,
    ) {
    }
}
