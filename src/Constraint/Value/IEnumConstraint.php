<?php

declare(strict_types=1);

namespace LMWF\Constraint\Value;

use LMWF\Constraint\IConstraint;

interface IEnumConstraint extends IConstraint
{
    /**
     * @return list<string> $allowedValues List of permitted string values.
     */
    public function getValues(): array;
}
