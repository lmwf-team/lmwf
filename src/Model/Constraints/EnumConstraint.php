<?php

declare(strict_types=1);

namespace LM\WebFramework\Model\Constraints;

final readonly class EnumConstraint implements IEnumConstraint
{
    private array $values;

    /**
     * @param \BackedEnum[] $enumCases List of permitted values.
     */
    public function __construct(array $enumCases)
    {
        $values = [];
        foreach ($enumCases as $c) {
            $values[] = $c->value;
        }
        $this->values = $values;
    }

    public function getValues(): array
    {
        return $this->values;
    }
}
