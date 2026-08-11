<?php

declare(strict_types=1);

namespace LM\WebFramework\Form\Transformer;

use LM\WebFramework\Form\Exceptions\MissingInputException;

final class IntTransformer implements IFormTransformer
{
    public function __construct(
        private string $name,
    ) {
    }

    /**
     * @return int|null The submitted, non-empty string, or null if the integer is empty.
     */
    public function extractValueFromRequest(array $formRawData, array $uploadedFiles): ?int
    {
        if (!key_exists($this->name, $formRawData)) {
            throw new MissingInputException($this->name);
        }
        $submtittedValue = $formRawData[$this->name];
        return '' !== $submtittedValue ? (int) $submtittedValue : null;
    }
}