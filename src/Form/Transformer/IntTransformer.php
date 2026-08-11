<?php

declare(strict_types=1);

namespace LMWF\Form\Transformer;

use LMWF\Form\Exceptions\MissingInputException;

final class IntTransformer extends AbstractStringTransformer implements IFormTransformer
{
    #[\Override]
    public function transformSubmittedData(array $parsedPayload, array $uploadedFiles): ?int
    {
        $appString = parent::extractTextInput($parsedPayload);
        return null !== $appString ? (int) $appString : null;
    }
}
