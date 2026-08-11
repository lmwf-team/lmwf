<?php

declare(strict_types=1);

namespace LM\WebFramework\Validator;

use LM\WebFramework\DataStructures\ConstraintViolation;
use LM\WebFramework\Model\Type\StringModel;

final class StringValidator implements ITypeValidator
{
    public function __construct(
        private StringModel $model,
    ) {
    }

    public function validate(mixed $value): array
    {
        if (!is_string($value)) {
            return [
                new ConstraintViolation(
                    $this->model,
                    'Data must be a string.',
                ),
            ];
        }
        $cvs = [];

        if (null !== $this->model->getRangeConstraint()) {
            $rangeValidator = new RangeValidator($this->model->getRangeConstraint());
            $cvs += $rangeValidator->validateString($value);
        }

        if (null !== $this->model->getRegexConstraint()) {
            $regexValidator = new RegexValidator($this->model->getRegexConstraint());
            $cvs += $regexValidator->validateString($value);
        }
        return $cvs;
    }
}