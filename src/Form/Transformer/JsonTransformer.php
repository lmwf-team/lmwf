<?php

declare(strict_types=1);

namespace LMWF\Form\Transformer;

final class JsonTransformer extends AbstractNamedTransformer
{
    /**
     * @todo Return an AppObject instead?
     */
    #[\Override]
    public function transformSubmittedData(array $parsedPayload, array $uploadedFiles): mixed
    {
        return json_decode($parsedPayload[$this->getName()], true); // @todo check flags
    }
}
