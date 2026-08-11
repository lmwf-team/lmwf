<?php

declare(strict_types=1);

namespace LM\WebFramework\Model\Type;

use LM\WebFramework\Model\Constraints\IRangeConstraint;
use LM\WebFramework\Model\Constraints\RangeConstraint;

final class IntModel extends AbstractModel implements IScalarModel
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

    public function getRangeConstraint(): ?IRangeConstraint
    {
        return $this->rangeConstraint;
    }
}
