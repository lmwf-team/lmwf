<?php

declare(strict_types=1);

namespace LM\WebFramework\Validator;

use LM\WebFramework\Constraints\IConstraint;
use LM\WebFramework\DataStructures\ConstraintViolation;

class NotNullValidator implements IValidator
{
    public function __construct(
        private IConstraint $constraint,
    ) {
    }

    public function validate(mixed $data): array {
        if (null === $data) {
            return [
                new ConstraintViolation($this->constraint, 'Data is not allowed to be null.'),
            ];
        }

        return [];
    }
}