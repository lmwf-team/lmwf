<?php

declare(strict_types=1);

namespace LM\WebFramework\Model\Constraints;

interface IRangeConstraint extends IConstraint
{
    public function getLowerLimit(): ?int;
    public function getUpperLimit(): ?int;
}