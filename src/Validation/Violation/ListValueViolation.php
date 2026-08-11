<?php

declare(strict_types=1);

namespace LM\WebFramework\Validation\Violation;

use LM\WebFramework\Constraint\Type\ArrayModel;
use LM\WebFramework\Constraint\Type\EntityListModel;
use LM\WebFramework\Constraint\Type\ListModel;

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
