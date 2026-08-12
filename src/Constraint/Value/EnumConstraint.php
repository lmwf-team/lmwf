<?php

declare(strict_types=1);

namespace LMWF\Constraint\Value;

use InvalidArgumentException;

/**
 * @todo For now, it only works with string-backed enums.
 */
final readonly class EnumConstraint implements IEnumConstraint
{
    /**
     * @var list<string>
     */
    private array $values;

    /**
     * @param list<\BackedEnum> $enumCases List of permitted values.
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
     * @return list<string>
     */
    #[\Override]
    public function getValues(): array
    {
        return $this->values;
    }
}
