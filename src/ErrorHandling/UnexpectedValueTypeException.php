<?php

declare(strict_types=1);

namespace LMWF\ErrorHandling;

use Throwable;

final class UnexpectedValueTypeException extends BaseUnexpectedValueException
{
    public function __construct(
        string $expectedType,
        mixed $actualValue,
        int $code,
        string $messageFmt = 'Expected value of type %1$s, got: %2$s.',
        Throwable|null $previous = null,
    ) {
        return parent::__construct(
            sprintf($messageFmt, $expectedType, $this->getStringDesc($actualValue)),
            $code,
            $previous,
        );
    }
}
