<?php

declare(strict_types=1);

namespace LM\WebFramework\Validation\Violation;

use LM\WebFramework\Constraint\Type\ArrayModel;
use LM\WebFramework\Constraint\Type\IModel;

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
