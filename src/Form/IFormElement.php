<?php

namespace LM\WebFramework\Form;

use LM\WebFramework\Form\Transformer\IFormTransformer;

/**
 * Defines a form data extractor with a name and an associated transformer.
 */
interface IFormElement extends IFormExtractor
{
    /**
     * @return string The form identifier for the form element.
     */
    public function getName(): string;

    public function getTransformer(): IFormTransformer;
}