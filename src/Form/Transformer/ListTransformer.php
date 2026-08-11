<?php

declare(strict_types=1);

namespace LM\WebFramework\Form\Transformer;

use LM\WebFramework\Form\Exceptions\ExtractionException;
use LM\WebFramework\Form\FormFactory;
use LM\WebFramework\Model\Type\IModel;
use LM\WebFramework\Model\Type\IScalarModel;

final class ListTransformer implements IFormTransformer
{
    public function __construct(
        private IModel $nodeModel,
        private array $nodeConfig,
        private FormFactory $formFactory,
        private string $name,
    ) {
    }

    public function transformSubmittedData(array $requestParsedBody, array $uploadedFiles): array
    {
        $data = $requestParsedBody[$this->name] ?? null;
        if (null === $data) {
            return [];
        }
        if (!is_array($data)) {
            throw new ExtractionException('Une erreur s’est produite.');
        }
        $value = [];
        foreach ($data as $name => $element) {
            if ($this->nodeModel instanceof IScalarModel) {
                $value[] = $this->formFactory
                    ->createTransformer($this->nodeModel, $this->nodeConfig, name: $name)
                    ->transformSubmittedData($element, $uploadedFiles)
                ;
            } else {
                $value[] = $this->formFactory
                    ->createTransformer($this->nodeModel, $this->nodeConfig, csrf: false)
                    ->transformSubmittedData($element, $uploadedFiles)
                ;
            }

        }

        return $value;
    }
}
