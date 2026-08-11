<?php

declare(strict_types=1);

namespace LMWF\Constraint\Value;

final readonly class StringEnumConstraint implements IEnumConstraint
{
    /**
     * @param list<string> $allowedValues List of permitted values.
     */
    public function __construct(
        private array $allowedValues,
    ) {
    }

    public function getValues(): array
    {
        return $this->allowedValues;
    }
}
