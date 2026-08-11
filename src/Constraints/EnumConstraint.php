<?php

declare(strict_types=1);

namespace LM\WebFramework\Constraints;

final class EnumConstraint implements IStringConstraint
{
    private array $values;

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