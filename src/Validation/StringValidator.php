<?php

declare(strict_types=1);

namespace LMWF\Validation;

use LMWF\Constraint\Value\IEnumConstraint;
use LMWF\Constraint\Value\IRegexConstraint;
use LMWF\Validation\Violation\IndividualViolation;
use LMWF\Constraint\Type\StringModel;
use LMWF\Validation\Violation\ScalarValueViolation;
use LMWF\Validation\Violation\TypeViolation;

final readonly class StringValidator extends AbstractTypeValidator
{
    public function __construct(
        private StringModel $model,
    ) {
        parent::__construct($model->getNotNullConstraint());
    }

    #[\Override]
    public function validateNonNullValue(array|bool|float|int|object|string $value): null|TypeViolation|ScalarValueViolation
    {
        if (!is_string($value)) {
            return new TypeViolation($this->model);
        }
        $violations = [];

        if (null !== $this->model->getRangeConstraint()) {
            $violations += new RangeValidator($this->model->getRangeConstraint())->validateString($value);
        }

        if (null !== $this->model->getRegexConstraint()) {
            $violations += $this->isRegexValid($value, $this->model->getRegexConstraint());
        }

        if (null !== $this->model->getEnumConstraint()) {
            $violations += $this->validateEnum($value, $this->model->getEnumConstraint());
        }

        if ([] === $violations) {
            return null;
        }
        return new ScalarValueViolation($violations);
    }

    /**
     * @return list<IndividualViolation>
     */
    private function validateEnum(string $value, IEnumConstraint $constraint): array
    {
        if (!in_array($value, $constraint->getValues(), true)) {
            return [
                new IndividualViolation($constraint),
            ];
        }
        return [];
    }

    /**
     * @return list<IndividualViolation>
     */
    public function isRegexValid(string $value, IRegexConstraint $constraint): array
    {
        if (1 !== preg_match('/' . $constraint->getRegex() . '/', $value)) {
            return [
                new IndividualViolation($constraint),
            ];
        }
        return [];
    }
}
