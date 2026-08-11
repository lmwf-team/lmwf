<?php

declare(strict_types=1);

namespace LM\WebFramework\Validation\Violation;

use LM\WebFramework\Constraint\IConstraint;
use Override;
use Stringable;

final readonly class ScalarValueViolation implements Stringable, ValueViolation
{
    /**
     * @param list<IndividualViolation> $violations
     */
    public function __construct(
        public array $violations,
        public string $message = 'Invalid value.',
    ) {
    }

    public function getCode(): ConstraintViolationCode
    {
        return ConstraintViolationCode::UNSPECIFIED;
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
