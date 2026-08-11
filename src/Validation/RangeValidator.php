<?php

declare(strict_types=1);

namespace LMWF\Validation;

use LMWF\Validation\Violation\IndividualViolation;
use LMWF\Constraint\Value\IRangeConstraint;

final class RangeValidator
{
    public function __construct(
        private IRangeConstraint $constraint,
    ) {
    }

    /**
     * @return list<IndividualViolation>
     */
    public function validateInt(int $value): array
    {
        $violations = [];
        if (null !== $this->constraint->getUpperLimit() && $value > $this->constraint->getUpperLimit()) {
            $violations[] =  new IndividualViolation($this->constraint, "$value is higher than set maximum.");
        }
        if (null !== $this->constraint->getLowerLimit() && $value < $this->constraint->getLowerLimit()) {
            $violations[] =  new IndividualViolation($this->constraint, "$value is lower than set minimum.");
        }
        return $violations;
    }

    /**
     * @return list<IndividualViolation>
     */
    public function validateString(string $value): array
    {
        $violations = [];
        if (null !== $this->constraint->getUpperLimit() && mb_strlen($value) > $this->constraint->getUpperLimit()) {
            $violations[] =  new IndividualViolation($this->constraint, "$value is higher than set maximum.");
        }
        if (null !== $this->constraint->getLowerLimit() && mb_strlen($value) < $this->constraint->getLowerLimit()) {
            $violations[] =  new IndividualViolation($this->constraint, "$value is lower than set minimum.");
        }
        return $violations;
    }
}
