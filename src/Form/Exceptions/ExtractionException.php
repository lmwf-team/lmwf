<?php

declare(strict_types=1);

namespace LMWF\Form\Exceptions;

use InvalidArgumentException;
use Throwable;

/**
 * Thrown by a submittable when no value could be extracted from the request.
 */
class ExtractionException extends InvalidArgumentException
{
    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
