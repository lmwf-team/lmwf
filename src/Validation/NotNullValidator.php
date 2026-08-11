<?php

declare(strict_types=1);

namespace LM\WebFramework\Validation;

use LM\WebFramework\Validation\ConstraintViolation\ConstraintViolation;
use LM\WebFramework\Model\Constraints\INotNullConstraint;

final class NotNullValidator implements ITypeValidator
{
    public function __construct(
        private INotNullConstraint $constraint,
    ) {
    }
    public function validate(mixed $data): array
    {
        if (null === $data) {
            return [
                new ConstraintViolation($this->constraint, 'Data is not allowed to be null.'),
            ];
        }

        return [];
    }
}
