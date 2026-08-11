<?php

declare(strict_types=1);

namespace LMWF\DataStructures\Exceptions;

use LMWF\ErrorHandling\ExceptionCode;
use Throwable;
use Override;
use UnexpectedValueException;

final class UnexpectedPropertyType extends UnexpectedValueException
{
    public function __construct(int|string $key, string $expectedType, Throwable|null $previous = null)
    {
        parent::__construct(
            "Property with key '$key' does not have the expected type '$expectedType'.",
            ExceptionCode::APP_TRAVERSABLE_UNEXPECTED_PROPERTY_TYPE->value,
            $previous,
        );
    }
}
