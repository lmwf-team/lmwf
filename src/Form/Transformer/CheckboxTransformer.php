<?php

declare(strict_types=1);

namespace LM\WebFramework\Form\Transformer;

final class CheckboxTransformer implements IFormTransformer
{
    public function __construct(
        private string $name,
    ) {
    }

    public function extractValueFromRequest(array $formRawData, array $uploadedFiles): bool
    {
        if (!isset($formRawData[$this->name])) {
            return false;
        }
        return 'on' === $formRawData[$this->name] ? true : false;
    }
}
