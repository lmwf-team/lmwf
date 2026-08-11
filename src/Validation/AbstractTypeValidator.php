<?php

declare(strict_types=1);

namespace LM\WebFramework\Validation;

use BadMethodCallException;
use InvalidArgumentException;
use LM\WebFramework\Constraint\Value\INotNullConstraint;
use LM\WebFramework\Constraint\Type\ArrayModel;
use LM\WebFramework\Validation\Violation\IndividualViolation;
use LM\WebFramework\Constraint\Type\IModel;
use LM\WebFramework\Constraint\Type\BoolModel;
use LM\WebFramework\Constraint\Type\DataArrayModel;
use LM\WebFramework\Constraint\Type\DateTimeModel;
use LM\WebFramework\Constraint\Type\EntityListModel;
use LM\WebFramework\Constraint\Type\EntityModel;
use LM\WebFramework\Constraint\Type\ForeignEntityModel;
use LM\WebFramework\Constraint\Type\IntModel;
use LM\WebFramework\Constraint\Type\IScalarModel;
use LM\WebFramework\Constraint\Type\ListModel;
use LM\WebFramework\Constraint\Type\StringModel;
use LM\WebFramework\Validation\Violation\ConstraintViolationCode;
use LM\WebFramework\Validation\Violation\TypeViolation;
use LM\WebFramework\Validation\Violation\ValueViolation;

/**
 * Abstract class that checks that the app data to validate is not null if the
 * model does not allow that, and then deleguates to the implementation method.
 */
abstract readonly class AbstractTypeValidator implements ITypeValidator
{
    public function __construct(
        private ?INotNullConstraint $notNullConstraint,
    ) {
    }

    public function validate(mixed $value): null|TypeViolation|ValueViolation
    {
        if (null === $value) {
            if (null !== $this->notNullConstraint) {
                return new TypeViolation(
                    $this->notNullConstraint,
                    'Data is not allowed to be null.',
                );
            }
            return null;
        }
        return $this->validateNonNullValue($value);
    }

    /**
     * @param list<mixed>|bool|float|int|object|string $value
     */
    abstract public function validateNonNullValue(array|bool|float|int|object|string $value): null|TypeViolation|ValueViolation;
}
