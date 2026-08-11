<?php

declare(strict_types=1);

namespace LMWF\Validation\Violation;

use LMWF\Constraint\Type\ArrayModel;
use LMWF\Constraint\Type\EntityListModel;
use LMWF\Constraint\Type\ListModel;

final class ListValueViolation implements ValueViolation
{
    /**
     * @param array<int, TypeViolation|ValueViolation> $itemViolations
     */
    public function __construct(
        public ListModel|EntityListModel $model,
        public array $itemViolations,
    ) {
    }

    public function getCode(): ConstraintViolationCode
    {
        return ConstraintViolationCode::ARRAY_ITEM;
    }

    public function getConstraint(): ListModel|EntityListModel
    {
        return $this->model;
    }
}
