<?php

namespace LM\WebFramework\Form\Transformer;

use LM\WebFramework\Form\Exceptions\ExtractionException;

class ArrayTransformer implements IFormTransformer
{
    /**
     * @param array<IFormTransformer> $formElements
     */
    public function __construct(
        private array $formElements,
        private ?CsrfTransformer $csrf = null,
        private ?string $name = null,
        private array $defaultCallbacks = [],
    ) {
    }

    public function extractValueFromRequest(array $requestParsedBody, array $uploadedFiles): array {
        $data = null === $this->name ? $requestParsedBody : $requestParsedBody[$this->name] ?? null;
        if (null === $data) {
            return [];
        }
        if (!is_array($data)) {
            throw new ExtractionException('Une erreur s’est produite.');
        }

        $values = [];
        foreach ($this->formElements as $key => $transformer) {
            $values[$key] = $transformer->extractValueFromRequest($data, $uploadedFiles);
        }
        foreach ($values as $key => $v) {
            if (null === $v && key_exists($key, $this->defaultCallbacks)) {
                $values[$key] = $this->defaultCallbacks[$key]($values);
            }
        }
        if (null !== $this->csrf) {
            $this->csrf->extractValueFromRequest($data, $uploadedFiles);
        }
        return $values;
    }
}