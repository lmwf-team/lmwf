<?php

namespace LM\WebFramework\Model;
use LM\WebFramework\Constraints\RangeConstraint;

class UintModel extends IntegerModel
{
    const MAX = 65535;

    public function __construct(
        private ?int $max = self::MAX,
        private bool $isNullable = false,
    ) {
        parent::__construct([new RangeConstraint(min: 0, max: $max)], $isNullable);
    }
}