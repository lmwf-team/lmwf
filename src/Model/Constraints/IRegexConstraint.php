<?php

declare(strict_types=1);

namespace LM\WebFramework\Model\Constraints;

interface IRegexConstraint extends IConstraint
{
    const MAX_LENGTH = 255;

    const REGEX_DASHES = '^(([a-z0-9])-?)*(?2)+$';

    public function getRegex(): string;
}