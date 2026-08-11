<?php

declare(strict_types=1);

namespace LM\WebFramework\Model\Constraints;

interface IRegexConstraint extends IConstraint
{
    public function getRegex(): string;
}
