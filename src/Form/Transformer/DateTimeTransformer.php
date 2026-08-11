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

    public function transformSubmittedData(array $postedData, array $uploadedFiles): ?DateTimeImmutable
    {
        if (!key_exists($this->name, $postedData)) {
            throw new MissingInputException();
        }

        $submittedString = $postedData[$this->name];

        return '' !== $submittedString ? new DateTimeImmutable($submittedString) : null;
    }
}
