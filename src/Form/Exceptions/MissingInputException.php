<?php

declare(strict_types=1);

namespace LMWF\Form\Exceptions;

use Throwable;

/**
 * Thrown when the submittable could not find any value from the request.
 */
class MissingInputException extends ExtractionException
{
    public function __construct(string $inputName, ?Throwable $previous = null)
    {
        parent::__construct("Input '{$inputName}' is missing.", previous: $previous);
    }
}
