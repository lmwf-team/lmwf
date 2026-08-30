<?php

declare(strict_types=1);

namespace LMWF\DataStructures\Exceptions;

use LMWF\ErrorHandling\BaseUnexpectedValueException;
use LMWF\ErrorHandling\ExceptionCode;
use Throwable;

final class UnexpectedPropertyType extends BaseUnexpectedValueException
{
    public function __construct(int|string $key, string $expectedType, mixed $actualValue, Throwable|null $previous = null)
    {
        $displayedActual = $this->getStringDesc($actualValue);

        parent::__construct(
            "Property with key '$key' does not have the expected type '$expectedType', got $displayedActual instead.",
            ExceptionCode::APP_TRAVERSABLE_UNEXPECTED_PROPERTY_TYPE->value,
            $previous,
        );
    }
}
