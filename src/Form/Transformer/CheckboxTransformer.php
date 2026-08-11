<?php

declare(strict_types=1);

namespace LM\WebFramework\Form\Transformer;

final class CheckboxTransformer implements IFormTransformer
{
    public function __construct(
        private string $name,
    ) {
    }

    public function transformSubmittedData(array $postedData, array $uploadedFiles): bool
    {
        if (!key_exists($this->name, $postedData)) {
            return false;
        }
        return 'on' === $postedData[$this->name] ? true : false;
    }
}
