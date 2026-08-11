<?php

declare(strict_types=1);

namespace LM\WebFramework\Validation;

use LM\WebFramework\Validation\ConstraintViolation\ConstraintViolation;
use LM\WebFramework\Model\Type\IntModel;

final class IntValidator implements ITypeValidator
{
    public function __construct(
        private IntModel $model,
    ) {
    }

    public function validate(mixed $value): array
    {
        $cvs = [];
        $rangeConstraint = $this->model->getRangeConstraint();
        if (!is_int($value)) {
            $cvs = [
                new ConstraintViolation($this->model, 'Value must be an integer.'),
            ];
        } elseif (null !== $rangeConstraint) {
            if (null !== $rangeConstraint->getLowerLimit() && $value < $rangeConstraint->getLowerLimit()) {
                $cvs[] = new ConstraintViolation($rangeConstraint, 'Value must be higher than ' . $rangeConstraint->getLowerLimit() . '.');
            }

            if (null !== $rangeConstraint->getLowerLimit() && $value > $rangeConstraint->getUpperLimit()) {
                $cvs[] = new ConstraintViolation($rangeConstraint, 'Value must be lower than ' . $rangeConstraint->getUpperLimit() . '.');
            }
        }
        return $cvs;
    }
}
