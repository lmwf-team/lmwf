<?php

declare(strict_types=1);

namespace LM\WebFramework\Constraint\Value;

use LM\WebFramework\Constraint\IConstraint;

interface IRangeConstraint extends IConstraint
{
    public function getLowerLimit(): ?int;
    public function getUpperLimit(): ?int;
}
