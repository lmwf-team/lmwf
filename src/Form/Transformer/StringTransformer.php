<?php

declare(strict_types=1);

namespace LMWF\Form\Transformer;

use InvalidArgumentException;

final class StringTransformer extends AbstractStringTransformer implements IFormTransformer
{
    #[\Override]
    public function transformSubmittedData(array $parsedPayload, array $uploadedFiles): ?string
    {
        return parent::extractTextInput($parsedPayload);
    }
}
