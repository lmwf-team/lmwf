<?php

declare(strict_types=1);

namespace LM\WebFramework\Model;

use LM\WebFramework\Constraints\RangeConstraint;

final class IntModel extends AbstractModel implements IScalarModel
{
    const MAX = 32767;

    const MAX_UNSIGNED = 65535;

    const MIN = -32767;

    private array $constraints;

    /**
     * @param LM\WebFramework\Constraints\INumberConstraint[] $constraints
     */
    public function __construct(
        ?int $min = null,
        ?int $max = null,
        bool $isNullable = false,
    ) {
        if (null !== $min || null !== $max) {
            $this->constraints = new RangeConstraint($min, $max);
        }
        parent::__construct(
            isNullable: $isNullable,
        );
    }

    public function getNumberConstraints(): array
    {
        return $this->constraints;
    }
}