<?php

declare(strict_types=1);

namespace LM\WebFramework\Form\Transformer;

/**
 * Transforms the data submitted with a POST request from its PHP representation
 * (parsedBody) to its representation in the app (formData).
 * @todo Add tests, with fuzzing.
 */
interface IFormTransformer
{
    /**
     * Extract the submitted value for the given field and convert it to the app
     * data format.
     *
     * It is not always possible to decide whether a value evaluating to null or
     * false was submitted or if no value was submitted because of a malformed
     * or non-valid request for instance.
     * For instance unchecked checkboxes submit the same value (actually, no
     * submitted value at all, not even null), than if the field did not exist
     * in the HTML form at all. A string left blank will evaluate to an empty
     * string and not to null.
     *
     * @return mixed The submitted value converted to the app data format, or
     * null if the user submitted a value evaluating to null.
     * @throws \LM\WebFramework\Form\Exceptions\MissingInputException If no value was submitted for the field.
     */
    public function transformSubmittedData(array $parsedPayload, array $uploadedFiles): mixed;
}
