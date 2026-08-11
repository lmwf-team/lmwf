<?php

declare(strict_types=1);

namespace LM\WebFramework\Validator;

use LM\WebFramework\DataStructures\ConstraintViolation;
use LM\WebFramework\Model\Type\BoolModel;

final class BoolValidator implements ITypeValidator
{
    public function __construct(
        private BoolModel $model,
    ) {
    }

    public function validate(mixed $value): array
    {
        if (!is_bool($value)) {
            return [
                new ConstraintViolation(
                    $this->model,
                    'Data must be a boolean.',
                ),
            ];
        }
        
        return [];
    }
}