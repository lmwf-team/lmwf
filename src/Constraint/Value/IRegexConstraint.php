<?php

declare(strict_types=1);

namespace LMWF\Constraint\Value;

use LMWF\Constraint\IConstraint;

interface IRegexConstraint extends IConstraint
{
    public function getRegex(): string;
}
