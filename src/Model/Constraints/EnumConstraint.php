<?php

declare(strict_types=1);

namespace LM\WebFramework\Model\Constraints;

final class EnumConstraint implements IStringConstraint
{
    private array $values;

    /**
     * @param enum[] $enumCases List of permitted values.
     */
    public function __construct(
        private array $enumCases,
    ) {
        $this->values = [];
        foreach ($enumCases as $c) {
            $this->values[] = $c->value;
        }
    }

    public function getValues(): array {
        return $this->values;
    }
}