<?php

declare(strict_types=1);

namespace LM\WebFramework\Constraint\Value;

use LM\WebFramework\Constraint\IConstraint;

interface IEnumConstraint extends IConstraint
{
    /**
     * @return list<string> $allowedValues List of permitted string values.
     */
    public function getValues(): array;
}
