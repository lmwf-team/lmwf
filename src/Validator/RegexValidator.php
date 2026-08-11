<?php

declare(strict_types=1);

namespace LM\WebFramework\Validator;

use LM\WebFramework\DataStructures\ConstraintViolation;
use LM\WebFramework\Model\Constraints\IRegexConstraint;

final class RegexValidator implements IStringValidator
{
    public function __construct(
        private IRegexConstraint $constraint,
    ) {
    }

    public function validateString(string $value): array
    {
        $violations = [];
        if (1 !== preg_match('/' . $this->constraint->getRegex() . '/', $value)) {
            $violations[] = new ConstraintViolation($this->constraint, "$value does not match format.");
        }
        return $violations;
    }
}