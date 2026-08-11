<?php

declare(strict_types=1);

namespace LM\WebFramework\Model\Constraints;

final class RegexConstraint implements IRegexConstraint
{
    const MAX_LENGTH = 255;

    const REGEX_DASHES = '^(([a-z0-9])-?)*(?2)+$';

    public function __construct(
        private string $regex,
    ) {
    }

    public function getRegex(): string
    {
        return $this->regex;
    }
}