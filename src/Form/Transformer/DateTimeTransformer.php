<?php

declare(strict_types=1);

namespace LM\WebFramework\Form\Transformer;

use DateTimeImmutable;
use LM\WebFramework\Form\Exceptions\MissingInputException;

final class DateTimeTransformer extends AbstractStringTransformer implements IFormTransformer
{
    #[\Override]
    public function transformSubmittedData(array $parsedPayload, array $uploadedFiles): ?DateTimeImmutable
    {
        $tmpFormData = parent::extractTextInput($parsedPayload);

        return null !== $tmpFormData ? new DateTimeImmutable($tmpFormData) : null;
    }
}
