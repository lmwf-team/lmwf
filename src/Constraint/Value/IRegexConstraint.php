<?php

declare(strict_types=1);

namespace LM\WebFramework\Constraint\Value;

use LM\WebFramework\Constraint\IConstraint;

interface IRegexConstraint extends IConstraint
{
    public function getRegex(): string;
}
