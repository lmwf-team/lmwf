<?php

declare(strict_types=1);

namespace LM\WebFramework\Form\Transformer;

use LM\WebFramework\Form\Exceptions\ExtractionException;

final class ArrayTransformer implements IFormTransformer
{
    /**
     * @param array<IFormTransformer> $fieldTransformers
     */
    public function __construct(
        private array $fieldTransformers,
        private ?CsrfTransformer $csrf = null,
        private ?string $name = null,
        private array $defaultCallbacks = [],
    ) {
    }

    /**
     * @return mixed[] An empty array if no data was submitted, or an associate arrays of transformed submitted data mapped by $fieldTransformers keys.
     */
    public function transformSubmittedData(array $requestParsedBody, array $uploadedFiles): array
    {
        $data = null === $this->name ? $requestParsedBody : $requestParsedBody[$this->name] ?? null;
        if (null === $data) {
            return [];
        }
        if (!is_array($data)) {
            throw new ExtractionException('Une erreur s’est produite.');
        }

        $values = [];
        foreach ($this->fieldTransformers as $key => $transformer) {
            $values[$key] = $transformer->transformSubmittedData($data, $uploadedFiles);
        }
        foreach ($values as $key => $v) {
            if (null === $v && key_exists($key, $this->defaultCallbacks)) {
                $values[$key] = $this->defaultCallbacks[$key]($values);
            }
        }
        if (null !== $this->csrf) {
            $this->csrf->transformSubmittedData($data, $uploadedFiles);
        }
        return $values;
    }
}
