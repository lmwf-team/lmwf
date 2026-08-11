<?php

declare(strict_types=1);

namespace LM\WebFramework\Validation;

use LM\WebFramework\Constraint\Value\IEnumConstraint;
use LM\WebFramework\Constraint\Value\IRegexConstraint;
use LM\WebFramework\Validation\Violation\IndividualViolation;
use LM\WebFramework\Constraint\Type\StringModel;
use LM\WebFramework\Validation\Violation\ScalarValueViolation;
use LM\WebFramework\Validation\Violation\TypeViolation;
use Override;

final readonly class StringValidator extends AbstractTypeValidator
{
    public function __construct(
        private StringModel $model,
    ) {
        parent::__construct($model->getNotNullConstraint());
    }

    #[Override]
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
