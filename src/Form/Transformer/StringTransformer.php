<?php

declare(strict_types=1);

namespace LM\WebFramework\Form\Transformer;

use LM\WebFramework\Form\Exceptions\MissingInputException;

final class StringTransformer extends AbstractStringTransformer implements IFormTransformer
{
    #[\Override]
    public function transformSubmittedData(array $parsedPayload, array $uploadedFiles): ?string
    {
        return parent::extractTextInput($parsedPayload);
    }
}
