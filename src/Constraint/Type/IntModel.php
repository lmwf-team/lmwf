<?php

declare(strict_types=1);

namespace LMWF\Constraint\Type;

use LMWF\Constraint\Value\IRangeConstraint;
use LMWF\Constraint\Value\RangeConstraint;

final class IntModel extends AbstractModel implements ILengthModel, IScalarModel
{
    public const MAX = 32767;

    public const MAX_UNSIGNED = 65535;

    public const MIN = -32767;

    private ?IRangeConstraint $rangeConstraint;

    public function __construct(
        ?int $min = null,
        ?int $max = null,
        bool $isNullable = false,
    ) {
        $this->rangeConstraint = (null !== $min || null !== $max) ? new RangeConstraint($min, $max) : null;

        parent::__construct(
            isNullable: $isNullable,
        );
    }

    #[\Override]
    public function getRangeConstraint(): ?IRangeConstraint
    {
        return $this->rangeConstraint;
    }
}
