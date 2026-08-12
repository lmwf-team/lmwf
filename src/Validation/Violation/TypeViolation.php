<?php

declare(strict_types=1);

namespace LMWF\Validation\Violation;

use LMWF\Constraint\IConstraint;
use LMWF\Constraint\Value\INotNullConstraint;
use LMWF\Constraint\Type\IModel;
use Stringable;

final readonly class TypeViolation implements IConstraintViolation, Stringable
{
    public function __construct(
        public IModel|INotNullConstraint $model,
        public string $message = 'Type not allowed.',
    ) {
    }

    public function getCode(): ConstraintViolationCode
    {
        return ConstraintViolationCode::UNAUTHORIZED_TYPE;
    }

    public function getConstraint(): IConstraint
    {
        return $this->model;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->message;
    }
}
