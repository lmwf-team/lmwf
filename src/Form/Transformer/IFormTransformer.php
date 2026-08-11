<?php

declare(strict_types=1);

namespace LM\WebFramework\Form\Transformer;

/**
 * Transforms submitted data into app data.
 * 
 * When data is entered in the form by the user and is submitted, PHP stores it in global arrays.
 * The data contained within these arrays are implemented as "form data".
 * Data is then transformed on the server-side by the PHP application by an IFormTransformer instance.
 * It then becomes "App Data", before it is validated.
*/
interface IFormTransformer
{
    /**
     * Transform user-submitted data from the request to form data.
     *
     * @param mixed[] The submitted data in the request.
     * @return mixed The form value.
     * @return null If the user submitted a null value (or a value that evalutates to a null form value).
     * @throws \LM\WebFramework\Form\Exceptions\MissingInputException If no value could be extracted from the request.
     */
    public function transformSubmittedData(array $formRawData, array $uploadedFiles): mixed;
}
