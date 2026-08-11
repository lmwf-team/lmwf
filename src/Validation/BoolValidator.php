<?php

declare(strict_types=1);

namespace LMWF\Validation;

use LMWF\Constraint\Type\BoolModel;
use LMWF\Validation\Violation\TypeViolation;

final readonly class BoolValidator extends AbstractTypeValidator
{
    public function __construct(
        private BoolModel $model,
    ) {
        parent::__construct($model->getNotNullConstraint());
    }

    #[\Override]
    public function validateNonNullValue(array|bool|float|int|object|string $value): null|TypeViolation
    {
        if (!is_bool($value)) {
            return new TypeViolation($this->model);
        }
        return null;
    }
}
