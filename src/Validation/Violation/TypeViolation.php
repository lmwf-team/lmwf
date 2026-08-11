<?php

declare(strict_types=1);

namespace LM\WebFramework\Validation\Violation;

use LM\WebFramework\Constraint\IConstraint;
use LM\WebFramework\Constraint\Value\INotNullConstraint;
use LM\WebFramework\Constraint\Type\IModel;
use Override;
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

    #[Override]
    public function __toString(): string
    {
        return $this->message;
    }
}
