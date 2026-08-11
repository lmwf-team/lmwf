<?php

namespace LM\WebFramework\Form\Transformer;

interface IFormTransformer
{
    /**
     * Extract, from the given submitted data, form data comprising a value and validation violations.
     *
     * @return mixed The submitted value transformed to a form value.
     * @return null If the user submitted a null value (or a submitted value that evalutates to a null form value).
     * @throws \LM\WebFramework\Form\Exceptions\MissingInputException If no value could be extracted from the request.
     */
    public function extractValueFromRequest(array $formRawData, array $uploadedFiles): mixed;
}