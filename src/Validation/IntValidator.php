<?php

declare(strict_types=1);

namespace LM\WebFramework\Validation;

use LM\WebFramework\Validation\ConstraintViolation\ConstraintViolation;
use LM\WebFramework\Model\Type\IntModel;

final class IntValidator implements ITypeValidator
{
    public function __construct(
        private IntModel $model,
    ) {
    }

    public function validate(mixed $value): array
    {
        if (!is_int($value)) {
            return [
                new ConstraintViolation($this->model, 'Value must be an integer.'),
            ];
        }
        return [];
    }
}