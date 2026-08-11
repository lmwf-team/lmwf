<?php

declare(strict_types=1);

namespace LM\WebFramework\Validation\Violation;

use LM\WebFramework\Constraint\IConstraint;
use LM\WebFramework\Constraint\Type\IModel;
use Override;
use Stringable;

/**
 * @todo Should be moved to Validator namespace.
 * @todo A code or enum should be added.
 */
final readonly class IndividualViolation implements Stringable, IConstraintViolation
{
    public function __construct(
        public IConstraint $constraint,
        public string $message = '',
        public ConstraintViolationCode $code = ConstraintViolationCode::UNSPECIFIED,
    ) {
    }

    public function getCode(): ConstraintViolationCode
    {
        return $this->code;
    }

    public function getConstraint(): IConstraint
    {
        return $this->constraint;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function __toString(): string
    {
        return $this->message;
    }
}
