<?php

declare(strict_types=1);

namespace LM\WebFramework\Form\Transformer;

use UnexpectedValueException;
use LM\WebFramework\ErrorHandling\Log;
use LM\WebFramework\Form\Exceptions\ExtractionException;

final class ArrayTransformer implements IFormTransformer
{
    /**
     * @param array<string, IFormTransformer> $fieldTransformers
     * @param array<string, IFormTransformer> $fieldDefaults Array that can be
     * empty. For any of the field defined in $fieldTransformers can be
     * associated default callback, that sets its value in case its transformer
     * evaluates to null.
     */
    public function __construct(
        private array $fieldTransformers,
        private ?CsrfTransformer $csrf = null,
        private array $fieldDefaults = [],
        private ?string $name = null,
    ) {
    }

    /**
     * @return mixed[] An empty array if no data was submitted, or an associate
     * arrays of transformed submitted data mapped by $fieldTransformers keys.
     */
    #[\Override]
    public function transformSubmittedData(array $parsedPayload, array $uploadedFiles): ?array
    {
        // Even if this transformer has a name, $parsedPayload[$this->name] might
        // still not exist if it contains fields that are not included in the
        // submitted value because they evaluate to false (checkboxes).
        $relevantParsedBody = null === $this->name ? $parsedPayload : ($parsedPayload[$this->name] ?? []);

        if (!is_array($relevantParsedBody)) {
            throw new UnexpectedValueException("The form with name {$this->name} is expected to be an array, got {get_class($this->name)} instead.");
        }

        $formData = [];
        foreach ($this->fieldTransformers as $key => $transformer) {
            $formData[$key] = $transformer->transformSubmittedData(
                $relevantParsedBody,
                $uploadedFiles,
            );
            Log::debug("Extracted value for field $key is {$formData[$key]}.");
        }
        foreach ($formData as $key => $value) {
            if (null === $value && key_exists($key, $this->fieldDefaults)) {
                $formData[$key] = $this->fieldDefaults[$key]($formData);
            }
        }
        if (null !== $this->csrf) {
            $this->csrf->transformSubmittedData($relevantParsedBody, $uploadedFiles);
        }
        return $formData;
    }
}
