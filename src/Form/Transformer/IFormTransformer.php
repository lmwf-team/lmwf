<?php

declare(strict_types=1);

namespace LM\WebFramework\Form\Transformer;

/**
 * Transforms the data submitted with a POST request from its PHP representation
 * (postedData) to its representation in the app (formData).
*/
interface IFormTransformer
{
    /**
     * Transform submitted data from the request to form data.
     *
     * @param mixed[] The submitted data in the request.
     * @return mixed The form value, or null if the user submitted a null value (or a value that evaluates to a null form value).
     * @throws \LM\WebFramework\Form\Exceptions\MissingInputException If no value could be extracted from the request.
     */
    public function transformSubmittedData(array $postedData, array $uploadedFiles): mixed;
}
