<?php

declare(strict_types=1);

namespace LM\WebFramework\Validation\Violation;

use LM\WebFramework\Constraint\Type\ArrayModel;

final class DictValueViolation implements ValueViolation
{
    /**
     * @param array<string, TypeViolation|ValueViolation|MissingItemViolation> $itemViolations
     */
    public function __construct(
        public ArrayModel $model,
        public array $itemViolations,
    ) {
    }

    public function getCode(): ConstraintViolationCode
    {
        return ConstraintViolationCode::ARRAY_ITEM;
    }

    public function getConstraint(): ArrayModel
    {
        return $this->model;
    }
}
