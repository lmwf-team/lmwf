<?php

declare(strict_types=1);

namespace LM\WebFramework\Form\Exceptions;

final class WrongCsrfException extends ExtractionException
{
    public function getUserErrorMessage(): string
    {
        return 'Le formulaire n’a pas pu être validé.';
    }
}
