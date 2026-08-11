<?php

declare(strict_types=1);

namespace LM\WebFramework\Form\Transformer;

use LM\WebFramework\Form\Exceptions\MissingInputException;
use LM\WebFramework\Form\Exceptions\WrongCsrfException;
use LM\WebFramework\Session\SessionManager;

final class CsrfTransformer implements IFormTransformer
{
    public const CSRF_FORM_ELEMENT_NAME = '_csrf';

    public function __construct(
        private SessionManager $session,
    ) {
    }

    public function extractValueFromRequest(array $formRawData, array $uploadedFiles): string
    {
        if (!isset($formRawData[self::CSRF_FORM_ELEMENT_NAME])) {
            throw new MissingInputException(self::CSRF_FORM_ELEMENT_NAME);
        }
        if ($this->session->getCsrf() !== $formRawData[self::CSRF_FORM_ELEMENT_NAME]) {
            throw new WrongCsrfException();
        }

        return $this->session->getCsrf();
    }
}
