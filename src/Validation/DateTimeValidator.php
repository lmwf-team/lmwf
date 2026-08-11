<?php

declare(strict_types=1);

namespace LM\WebFramework\Validation;

use DateTimeInterface;
use LM\WebFramework\Constraint\Type\DateTimeModel;
use LM\WebFramework\Validation\Violation\TypeViolation;

final readonly class DateTimeValidator extends AbstractTypeValidator
{
    public function __construct(
        private DateTimeModel $model,
    ) {
        parent::__construct($model->getNotNullConstraint());
    }

    #[\Override]
    public function validateNonNullValue(array|bool|float|int|object|string $value): null|TypeViolation
    {
        if (!($value instanceof DateTimeInterface)) {
            return new TypeViolation($this->model);
        }
        return null;
    }
}
