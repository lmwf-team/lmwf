<?php

declare(strict_types=1);

namespace LMWF\Validation;

use LMWF\Validation\Violation\IndividualViolation;
use LMWF\Constraint\Type\IntModel;
use LMWF\Validation\Violation\ScalarValueViolation;
use LMWF\Validation\Violation\TypeViolation;
use LMWF\Validation\Violation\ValueViolation;
use Override;

final readonly class IntValidator extends AbstractTypeValidator
{
    public function __construct(
        private IntModel $model,
    ) {
        parent::__construct($model->getNotNullConstraint());
    }

    #[Override]
    public function validateNonNullValue(array|bool|float|int|object|string $value): null|TypeViolation|ValueViolation
    {
        if (!is_int($value)) {
            return new TypeViolation($this->model);
        }

        $rangeConstraint = $this->model->getRangeConstraint();
        $violations = [];
        if (null !== $rangeConstraint) {
            if (null !== $rangeConstraint->getLowerLimit() && $value < $rangeConstraint->getLowerLimit()) {
                $violations[] = new IndividualViolation($rangeConstraint, 'Value must be higher than ' . $rangeConstraint->getLowerLimit() . '.');
            }

            if (null !== $rangeConstraint->getUpperLimit() && $value > $rangeConstraint->getUpperLimit()) {
                $violations[] = new IndividualViolation($rangeConstraint, 'Value must be lower than ' . $rangeConstraint->getUpperLimit() . '.');
            }
        }
        return [] === $violations ? null : new ScalarValueViolation($violations);
    }
}
