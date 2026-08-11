<?php

declare(strict_types=1);

namespace LM\WebFramework\Model\Constraints;

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

    public function getLowerLimit(): ?int {
        return $this->lowerLimit;
    }

    public function getUpperLimit(): ?int {
        return $this->upperLimit;
    }
}