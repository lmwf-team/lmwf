<?php

declare(strict_types=1);

namespace LMWF\ErrorHandling;

use Override;
use Throwable;

final class UnexpectedValueTypeException extends BaseUnexpectedValueException
{
    public function __construct(string $expectedType, mixed $actualValue, int $code, Throwable|null $previous = null)
    {
        return parent::__construct(
            "Expected value of type {$expectedType}, got {$this->getStringDesc($actualValue)}.",
            $code,
            $previous,
        );
    }
}
