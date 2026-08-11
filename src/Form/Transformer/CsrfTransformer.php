<?php

declare(strict_types=1);

namespace LMWF\Form\Transformer;

use LMWF\Form\Exceptions\MissingInputException;
use LMWF\Form\Exceptions\WrongCsrfException;
use LMWF\Session\SessionManager;

final class CsrfTransformer implements IFormTransformer
{
    public const CSRF_FORM_ELEMENT_NAME = '_csrf';

    public function __construct(
        private SessionManager $session,
    ) {
    }

    #[\Override]
    public function transformSubmittedData(array $parsedPayload, array $uploadedFiles): string
    {
        if (!key_exists(self::CSRF_FORM_ELEMENT_NAME, $parsedPayload)) {
            throw new MissingInputException(self::CSRF_FORM_ELEMENT_NAME);
        }
        if ($this->session->getCsrf() !== $parsedPayload[self::CSRF_FORM_ELEMENT_NAME]) {
            throw new WrongCsrfException();
        }

        return $this->session->getCsrf();
    }
}
