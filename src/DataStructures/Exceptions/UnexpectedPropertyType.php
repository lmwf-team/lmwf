<?php

declare(strict_types=1);

namespace LMWF\DataStructures\Exceptions;

use LMWF\ErrorHandling\ExceptionCode;
use Throwable;
use Override;
use UnexpectedValueException;

final class UnexpectedPropertyType extends UnexpectedValueException
{
    public function __construct(int|string $key, string $expectedType, mixed $actualValue, Throwable|null $previous = null)
    {
        $actualType = gettype($actualValue);
        if (is_object($actualValue)) {
            $actualType = get_class($actualValue);
        }
        parent::__construct(
            "Property with key '$key' does not have the expected type '$expectedType', got '$actualType' instead.",
            ExceptionCode::APP_TRAVERSABLE_UNEXPECTED_PROPERTY_TYPE->value,
            $previous,
        );
    }
}
