<?php

declare(strict_types=1);

namespace LMWF\Constraint\Value;

use LMWF\Constraint\IConstraint;

interface IRangeConstraint extends IConstraint
{
    public function getLowerLimit(): ?int;
    public function getUpperLimit(): ?int;
}
