<?php

declare(strict_types=1);

namespace LMWF\Validation\Violation;

use LMWF\Constraint\Type\ArrayModel;
use LMWF\Constraint\Type\IModel;

final class MissingItemViolation implements ValueViolation
{
    public function __construct(
        public IModel $itemModel,
    ) {
    }

    public function getCode(): ConstraintViolationCode
    {
        return ConstraintViolationCode::ARRAY_ITEM;
    }

    public function getConstraint(): IModel
    {
        return $this->itemModel;
    }
}
