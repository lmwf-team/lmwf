<?php

declare(strict_types=1);

namespace LM\WebFramework\Form\Transformer;

use LM\WebFramework\Form\Exceptions\MissingInputException;

final class IntTransformer extends AbstractStringTransformer implements IFormTransformer
{
    #[\Override]
    public function transformSubmittedData(array $parsedPayload, array $uploadedFiles): ?int
    {
        $appString = parent::extractTextInput($parsedPayload);
        return null !== $appString ? (int) $appString : null;
    }
}
