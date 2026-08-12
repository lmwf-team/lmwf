<?php

declare(strict_types=1);

namespace LMWF\Validation;

use LMWF\Constraint\Type\ArrayModel;
use LMWF\Validation\Violation\DictValueViolation;
use LMWF\Validation\Violation\MissingItemViolation;
use LMWF\Validation\Violation\TypeViolation;
use LMWF\Validation\Violation\ValueViolation;

final readonly class ArrayValidator implements ITypeValidator
{
    public function __construct(
        private ArrayModel $model,
    ) {
    }

    #[\Override]
    /**
     * @return ($value is array ? DictValueViolation : TypeViolation)
     */
    public function validate(mixed $value): null|TypeViolation|DictValueViolation
    {
        if (!is_array($value)) {
            if (null === $value && false === $this->model->isNullable()) {
                return new TypeViolation($this->model->getNotNullConstraint(), 'Data is not allowed to be null.');
            }
            return new TypeViolation($this->model);
        }

        $validatorFactory = new ValidatorFactory();
        $violations = [];
        foreach ($this->model->getProperties() as $key => $model) {
            $validationResult = key_exists($key, $value) ?
                $validatorFactory->create($model)->validate($value[$key]) :
                new MissingItemViolation($model);
            if ($validationResult instanceof TypeViolation or $validationResult instanceof ValueViolation) {
                $violations[$key] = $validationResult;
            }
        }

        if ([] === $violations) {
            return null;
        }
        return new DictValueViolation($this->model, $violations);
    }
}
