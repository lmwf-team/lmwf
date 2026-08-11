<?php

declare(strict_types=1);

namespace LM\WebFramework\Form\Exceptions;

use InvalidArgumentException;
use Throwable;

/**
 * Thrown by a submittable when no value could be extracted from the request.
 */
class ExtractionException extends InvalidArgumentException
{
    private string $userErrorMessage;

    public function __construct(
        string $message = '',
        int $code = 0,
        Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string A message to display to the user.
     */
    public function getUserErrorMessage(): string {
        return $this->userErrorMessage;
    }
}