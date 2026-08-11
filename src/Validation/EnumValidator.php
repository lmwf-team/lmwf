<?php

declare(strict_types=1);

namespace LM\WebFramework\Validation;

use LM\WebFramework\Model\Constraints\EnumConstraint;
use LM\WebFramework\Validation\ConstraintViolation\ConstraintViolation;

final class EnumValidator implements ITypeValidator
{
    public function __construct(
        private EnumConstraint $constraint,
    ) {
    }

    public function validate(mixed $data): array
    {
        if (!in_array($data, $this->constraint->getValues(), true)) {
            return [
                new ConstraintViolation($this->constraint, 'Data does not adhere to the list of permitted values.'),
            ];
        }
        return [];
    }
}
