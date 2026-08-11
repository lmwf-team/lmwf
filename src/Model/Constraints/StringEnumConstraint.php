<?php

declare(strict_types=1);

namespace LM\WebFramework\Model\Constraints;

final readonly class StringEnumConstraint implements IEnumConstraint
{
    /**
     * @param string[] $allowedValues List of permitted values.
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
