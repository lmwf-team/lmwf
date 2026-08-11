<?php

declare(strict_types=1);

namespace LM\WebFramework\Validation\ConstraintViolation;

use LM\WebFramework\Model\Constraints\IConstraint;
use Stringable;

/**
 * @todo Should be moved to Validator namespace.
 */
final class ConstraintViolation implements Stringable
{
    public function __construct(
        private IConstraint $constraint,
        private ?string $message = null,
    ) {
    }

    public function getMessage(): ?string {
        return $this->message;
    }

    public function __toString(): string {
        return $this->message ?? $this->constraint::class . 'failed.';
    }
}