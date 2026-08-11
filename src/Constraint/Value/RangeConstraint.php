<?php

declare(strict_types=1);

namespace LMWF\Constraint\Value;

use InvalidArgumentException;

final class RangeConstraint implements IRangeConstraint
{
    public function __construct(
        private ?int $lowerLimit = 0,
        private ?int $upperLimit = null,
    ) {
        if (null !== $lowerLimit && null !== $upperLimit && $lowerLimit > $upperLimit) {
            throw new InvalidArgumentException('Min cannot be higher than max.');
        } elseif (null === $lowerLimit && null === $upperLimit) {
            throw new InvalidArgumentException('Both the lower and upper limits cannot be null.');
        }
    }

    #[\Override]
    public function getLowerLimit(): ?int
    {
        return $this->lowerLimit;
    }

    #[\Override]
    public function getUpperLimit(): ?int
    {
        return $this->upperLimit;
    }
}
