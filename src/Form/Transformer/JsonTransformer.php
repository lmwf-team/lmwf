<?php

declare(strict_types=1);

namespace LM\WebFramework\Form\Transformer;

final class JsonTransformer extends AbstractNamedTransformer
{
    /**
     * @todo Return an AppObject instead?
     */
    public function transformSubmittedData(array $formRawData, array $uploadedFiles): mixed
    {
        return json_decode($formRawData[$this->getName()], true); // @todo check flags
    }
}
