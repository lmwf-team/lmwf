<?php

declare(strict_types=1);

namespace LM\WebFramework\Validation;

use DateTimeInterface;
use LM\WebFramework\Validation\ConstraintViolation\ConstraintViolation;
use LM\WebFramework\Model\Type\DateTimeModel;

final class DateTimeValidator implements ITypeValidator
{
    public function __construct(
        private DateTimeModel $model,
    ) {
    }

    public function validate(mixed $value): array
    {
        if (!$value instanceof DateTimeInterface) {
            return [
                new ConstraintViolation(
                    $this->model,
                    'Data must be a DateTimeInterface instance.',
                ),
            ];
        }

        return [];
    }
}
