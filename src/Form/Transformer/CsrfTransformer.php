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

    #[Override]
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
