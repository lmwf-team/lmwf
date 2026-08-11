<?php

declare(strict_types=1);

namespace LM\WebFramework\Validation;

use LM\WebFramework\Validation\ConstraintViolation\ConstraintViolation;
use LM\WebFramework\Model\Constraints\IRangeConstraint;

final class RangeValidator implements IStringValidator, IIntValidator
{
    public function __construct(
        private IRangeConstraint $constraint,
    ) {
    }

    public function validateInt(int $value): array {
        $violations = [];
        if (null !== $this->constraint->getUpperLimit() && $value > $this->constraint->getUpperLimit()) {
            $violations[] =  new ConstraintViolation($this->constraint, "$value is higher than set maximum.");
        }
        if (null !== $this->constraint->getLowerLimit() && $value < $this->constraint->getLowerLimit()) {
            $violations[] =  new ConstraintViolation($this->constraint, "$value is lower than set minimum.");
        }
        return $violations;
    }

    public function validateString(string $value): array {
        $violations = [];
        if (null !== $this->constraint->getUpperLimit() && mb_strlen($value) > $this->constraint->getUpperLimit()) {
            $violations[] =  new ConstraintViolation($this->constraint, "$value is higher than set maximum.");
        }
        if (null !== $this->constraint->getLowerLimit() && mb_strlen($value) < $this->constraint->getLowerLimit()) {
            $violations[] =  new ConstraintViolation($this->constraint, "$value is lower than set minimum.");
        }
        return $violations;
    }
}