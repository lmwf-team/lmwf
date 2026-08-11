<?php

declare(strict_types=1);

namespace LM\WebFramework\Form\Transformer;

use UnexpectedValueException;

final class CheckboxTransformer implements IFormTransformer
{
    public function __construct(
        private string $name,
    ) {
    }

    #[\Override]
    public function transformSubmittedData(array $parsedPayload, array $uploadedFiles): bool
    {
        if (!key_exists($this->name, $parsedPayload)) {
            return false;
        }
        if ('on' === $parsedPayload[$this->name]) {
            return true;
        }
        throw new UnexpectedValueException("Unexpected value submitted for field with name {$this->name}.");
    }
}
