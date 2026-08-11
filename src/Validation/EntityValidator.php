<?php

declare(strict_types=1);

namespace LM\WebFramework\Validation;

use LM\WebFramework\Constraint\Type\ArrayModel;
use LM\WebFramework\Validation\Violation\DictValueViolation;
use LM\WebFramework\Validation\Violation\IndividualViolation;
use LM\WebFramework\Validation\Violation\MissingItemViolation;
use LM\WebFramework\Validation\Violation\TypeViolation;
use LM\WebFramework\Validation\Violation\ValueViolation;

final readonly class EntityValidator extends AbstractTypeValidator
{
    public function __construct(
        private ArrayModel $model,
    ) {
        parent::__construct($model->getNotNullConstraint());
    }

    #[\Override]
    public function validateNonNullValue(array|bool|float|int|object|string $value): null|TypeViolation|DictValueViolation
    {
        if (!is_array($value)) {
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
