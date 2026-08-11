<?php

declare(strict_types=1);

namespace LMWF\Validation;

use LMWF\Constraint\Type\EntityListModel;
use LMWF\Constraint\Type\ListModel;
use LMWF\Validation\Violation\ListValueViolation;
use LMWF\Validation\Violation\TypeViolation;
use LMWF\Validation\Violation\ValueViolation;
use Override;

final readonly class ListValidator extends AbstractTypeValidator
{
    public function __construct(
        private ListModel|EntityListModel $model,
    ) {
        parent::__construct($model->getNotNullConstraint());
    }

    #[Override]
    public function validateNonNullValue(array|bool|float|int|object|string $value): null|TypeViolation|ValueViolation
    {
        if (!is_array($value) || !array_is_list($value)) {
            return new TypeViolation($this->model);
        }

        $validator = new ValidatorFactory()->create($this->model->getItemModel());
        $violations = [];
        foreach ($value as $key => $item) {
            $validationResult = $validator->validate($item);
            if ($validationResult instanceof TypeViolation or $validationResult instanceof ValueViolation) {
                $violations[$key] = $validationResult;
            }
        }

        if ([] !== $violations) {
            return new ListValueViolation($this->model, $violations);
        }
        return null;
    }
}
