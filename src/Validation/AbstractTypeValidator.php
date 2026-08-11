<?php

declare(strict_types=1);

namespace LMWF\Validation;

use BadMethodCallException;
use InvalidArgumentException;
use LMWF\Constraint\Value\INotNullConstraint;
use LMWF\Constraint\Type\ArrayModel;
use LMWF\Validation\Violation\IndividualViolation;
use LMWF\Constraint\Type\IModel;
use LMWF\Constraint\Type\BoolModel;
use LMWF\Constraint\Type\DataArrayModel;
use LMWF\Constraint\Type\DateTimeModel;
use LMWF\Constraint\Type\EntityListModel;
use LMWF\Constraint\Type\EntityModel;
use LMWF\Constraint\Type\ForeignEntityModel;
use LMWF\Constraint\Type\IntModel;
use LMWF\Constraint\Type\IScalarModel;
use LMWF\Constraint\Type\ListModel;
use LMWF\Constraint\Type\StringModel;
use LMWF\Validation\Violation\ConstraintViolationCode;
use LMWF\Validation\Violation\TypeViolation;
use LMWF\Validation\Violation\ValueViolation;

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
