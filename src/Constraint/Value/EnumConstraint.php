<?php

declare(strict_types=1);

namespace LM\WebFramework\Constraint\Value;

use InvalidArgumentException;
use LM\WebFramework\Constraint\IConstraint;

final readonly class EnumConstraint implements IConstraint
{
    /**
     * @property string[] $values
     */
    private array $values;

    /**
     * @param \BackedEnum[] $enumCases List of permitted values.
     */
    public function __construct(array $enumCases)
    {
        $values = [];
        foreach ($enumCases as $c) {
            if (!is_string($c->value)) {
                throw new InvalidArgumentException('Enum must be string-backed.');
            }
            $values[] = $c->value;
        }
        $this->values = $values;
    }

    /**
     * @return string[]
     */
    public function getValues(): array
    {
        return $this->values;
    }
}
