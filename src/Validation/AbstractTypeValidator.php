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
 * Validator for app data.
 *
 * Validates the data is not null if the model does not allow that, that it is
 * of the correct type and that the value is allowed.
 */
abstract readonly class AbstractTypeValidator implements ITypeValidator
{
    public function __construct(
        private ?INotNullConstraint $notNullConstraint,
    ) {
    }

    #[\Override]
    public function validate(mixed $value): null|TypeViolation|ValueViolation
    {
        if (null !== $value) {
            /**
             * This is because PHPStan does not yet distinguish between the
             * mixed data type and the non-nullable mixed data type.
             * @phpstan-ignore argument.type
             */
            return $this->validateNonNullValue($value);
        }
        if (null !== $this->notNullConstraint) {
            return new TypeViolation(
                $this->notNullConstraint,
                'Data is not allowed to be null.',
            );
        }
        return null;
    }

    /**
     * @param object|array<mixed>|string|float|int|bool $value
     */
    abstract public function validateNonNullValue(array|bool|float|int|object|string $value): null|TypeViolation|ValueViolation;
}
