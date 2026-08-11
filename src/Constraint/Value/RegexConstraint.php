<?php

declare(strict_types=1);

namespace LMWF\Constraint\Value;

final class RegexConstraint implements IRegexConstraint
{
    public function __construct(
        private string $regex,
    ) {
    }

    public function getRegex(): string
    {
        return $this->regex;
    }
}
