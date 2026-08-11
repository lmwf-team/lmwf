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
    public function transformSubmittedData(array $postedData, array $uploadedFiles): ?int
    {
        if (!key_exists($this->name, $postedData)) {
            throw new MissingInputException($this->name);
        }
        $submtittedValue = $postedData[$this->name];
        return '' !== $submtittedValue ? (int) $submtittedValue : null;
    }
}
