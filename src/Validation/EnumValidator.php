<?php

declare(strict_types=1);

namespace LM\WebFramework\Validation;

use LM\WebFramework\Model\Constraints\IEnumConstraint;
use LM\WebFramework\Validation\ConstraintViolation\ConstraintViolation;

final class EnumValidator implements ITypeValidator
{
    public function __construct(
        private IEnumConstraint $constraint,
    ) {
    }

    public function validate(mixed $data): array
    {
        if (!in_array($data, $this->constraint->getValues(), true)) {
            if (0 === count($this->constraint->getValues())) {
                return [
                    new ConstraintViolation($this->constraint, 'Data does not adhere to the list of permitted values, which is empty.'),
                ];
            } elseif (1 === count($this->constraint->getValues())) {
                $onlyAllowedValue = array_first($this->constraint->getValues());
                return [
                    new ConstraintViolation($this->constraint, "Data must be equal to '{$onlyAllowedValue}'."),
                ];
            }
            return [
                new ConstraintViolation($this->constraint, 'Data does not adhere to the list of permitted values.'),
            ];
        }
        return [];
    }
}
