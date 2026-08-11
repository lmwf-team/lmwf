<?php

declare(strict_types=1);

namespace LM\WebFramework\Validator;

use LM\WebFramework\DataStructures\ConstraintViolation;
use LM\WebFramework\Model\Constraints\IRangeConstraint;

final class RangeValidator implements IStringValidator, IIntValidator
{
    public function __construct(
        private IRangeConstraint $constraint,
    ) {
    }

    public function validateInt(int $value): array {
        $violations = [];
        if (null !== $this->constraint->getMax() && $value > $this->constraint->getMax()) {
            $violations[] =  new ConstraintViolation($this->constraint, "$value is higher than set maximum.");
        }
        if (null !== $this->constraint->getMin() && $value < $this->constraint->getMin()) {
            $violations[] =  new ConstraintViolation($this->constraint, "$value is lower than set minimum.");
        }
        return $violations;
    }

    public function validateString(string $value): array {
        $violations = [];
        if (null !== $this->constraint->getMax() && mb_strlen($value) > $this->constraint->getMax()) {
            $violations[] =  new ConstraintViolation($this->constraint, "$value is higher than set maximum.");
        }
        if (null !== $this->constraint->getMin() && mb_strlen($value) < $this->constraint->getMin()) {
            $violations[] =  new ConstraintViolation($this->constraint, "$value is lower than set minimum.");
        }
        return $violations;
    }
}