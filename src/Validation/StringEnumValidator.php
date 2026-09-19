<?php

declare(strict_types=1);

namespace LMWF\Validation;

use LMWF\Constraint\Type\StringEnumModel;
use LMWF\Constraint\Value\IEnumConstraint;
use LMWF\Constraint\Value\IRegexConstraint;
use LMWF\Validation\Violation\IndividualViolation;
use LMWF\Constraint\Type\StringModel;
use LMWF\Validation\Violation\ScalarValueViolation;
use LMWF\Validation\Violation\TypeViolation;

final readonly class StringEnumValidator extends AbstractTypeValidator
{
    public function __construct(
        private StringEnumModel $model,
    ) {
        parent::__construct($model->getNotNullConstraint());
    }

    #[\Override]
    public function validateNonNullValue(array|bool|float|int|object|string $value): null|TypeViolation|ScalarValueViolation
    {
        foreach ($this->model->cases as $case) {
            if ($case === $value) {
                return null;
            }
        }

        return new TypeViolation($this->model, 'Value must be exactly one of the provided enum cases.');
    }
}
