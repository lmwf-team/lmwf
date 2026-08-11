<?php

declare(strict_types=1);

namespace LM\WebFramework\Model\Constraints;

use InvalidArgumentException;

final class RangeConstraint implements IRangeConstraint
{
    public function __construct(
        private ?int $min = 0,
        private ?int $max = null,
    ) {
        if (null !== $min && null !== $max && $min > $max) {
            throw new InvalidArgumentException('Min cannot be higher than max.');
        }
    }

    public function getMin(): ?int {
        return $this->min;
    }

    public function getMax(): ?int {
        return $this->max;
    }
}