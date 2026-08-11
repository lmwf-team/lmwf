<?php

declare(strict_types=1);

namespace LM\WebFramework\Form\Transformer;

use DateTimeImmutable;
use LM\WebFramework\Form\Exceptions\MissingInputException;

final class DateTimeTransformer implements IFormTransformer
{
    public function __construct(
        private string $name,
    ) {
    }

    public function extractValueFromRequest(array $formRawData, array $uploadedFiles): ?DateTimeImmutable {
        if (!key_exists($this->name, $formRawData)) {
            throw new MissingInputException();
        }

        $submittedString = $formRawData[$this->name];

        return '' !== $submittedString ? new DateTimeImmutable($submittedString) : null;
    }
}