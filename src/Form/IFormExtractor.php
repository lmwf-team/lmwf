<?php

declare(strict_types=1);

namespace LM\WebFramework\Form;

use LM\WebFramework\Form\DataStructures\IFormData;
use LM\WebFramework\Form\Exceptions\ExtractionException;

/**
 * Extract and validate submitted form data from a request.
 */
interface IFormExtractor
{
    /**
     * @return \LM\WebFramework\Form\DataStructures\IFormData An object containing the submitted data alongside any validation failures if relevant.
     * If no data could be extracted data (not even null), it MUST throw an ExtractionException.
     * An IFormData instance also comes with an array of validation failures, which should be empty.
     * @throws \LM\WebFramework\Form\Exceptions\ExtractionException If no submitted value could not found, or the found value could not be extracted.
     */
    public function extractFromRequest(array $requestFormData, ?array $uploadedFiles): IFormData;
}