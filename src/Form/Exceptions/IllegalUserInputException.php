<?php

declare(strict_types=1);

namespace LM\WebFramework\Form\Exceptions;

/**
 * Thrown by a submittable when no value could be extracted from the request.
 */
final class IllegalUserInputException extends ExtractionException
{
    private string $userErrorMessage;

    public function __construct(
    ) {
        parent::__construct('Such a value is not authorized.');
    }
}
