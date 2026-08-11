<?php

declare(strict_types=1);

namespace LM\WebFramework\Validator;

use LM\WebFramework\DataStructures\ConstraintViolation;
use LM\WebFramework\Model\Type\EntityModel;

class EntityValidator implements ITypeValidator
{
    public function __construct(
        private EntityModel $model,
    ) {
    }

    public function validate(mixed $value): array
    {
        if (!is_array($value)) {
            return [
                new ConstraintViolation($this->model, 'Value must be an associative array.'),
            ];
        }

        $violations = [];
        foreach ($this->model->getProperties() as $key => $model) {
            if (key_exists($key, $value)) {
                $propertyViolations = (new ModelValidator($model))->validate($value[$key]);
            } else {
                $propertyViolations = [
                    new ConstraintViolation($this->model, "Property {$key} is not defined."),
                ];
            }
            $violations += $propertyViolations;
        }

        return $violations;
    }
}