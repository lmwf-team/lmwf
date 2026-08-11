<?php

declare(strict_types=1);

namespace LM\WebFramework\Validation\ConstraintViolation;

use LM\WebFramework\Model\Constraints\IConstraint;
use Stringable;

/**
 * @todo Should be moved to Validator namespace.
 */
final readonly class ConstraintViolation implements Stringable
{
    public function __construct(
        public IConstraint $constraint,
        public string $message,
    ) {
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
