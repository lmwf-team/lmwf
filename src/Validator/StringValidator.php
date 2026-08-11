<?php

namespace LM\WebFramework\Validator;

use LM\WebFramework\Constraints\StringConstraint;
use LM\WebFramework\DataStructures\ConstraintViolation;

class StringValidator implements IValidator
{
    public function __construct(
        private StringConstraint $constraint,
    ) {
    }

    public function validate(mixed $data): array {
        $cvs = [];
        if (mb_strlen($data) < $this->constraint->getMinLength()) {
            $cvs[] = new ConstraintViolation($this->constraint, "$data is too short.");
        } elseif (null !== $this->constraint->getMaxLength() && mb_strlen($data) > $this->constraint->getMaxLength()) {
            $cvs[] = new ConstraintViolation($this->constraint, "$data is too long.");
        }
        if (null !== $this->constraint->getRegex() && 1 !== preg_match('/' . $this->constraint->getRegex() . '/', $data)) {
            $cvs[] = new ConstraintViolation($this->constraint, "$data does not match format.");
        }
        return $cvs;
    }
}