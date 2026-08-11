<?php

declare(strict_types=1);

namespace LM\WebFramework\Form\Transformer;

use LM\WebFramework\Form\Conf\FormFieldConf;
use LM\WebFramework\Form\Exceptions\ExtractionException;
use LM\WebFramework\Form\FormFactory;
use LM\WebFramework\Model\Type\IModel;
use LM\WebFramework\Model\Type\IScalarModel;

final class ListTransformer implements IFormTransformer
{
    public function __construct(
        private array|FormFieldConf $nodeConf,
        private FormFactory $formFactory,
        private string $name,
    ) {
    }

    #[\Override]
    public function transformSubmittedData(array $parsedPayload, array $uploadedFiles): array
    {
        $data = $parsedPayload[$this->name] ?? null;
        if (null === $data) {
            return [];
        }
        if (!is_array($data)) {
            throw new ExtractionException('Une erreur s’est produite.');
        }
        $value = [];
        foreach (array_keys($data) as $fieldId) {
            if ($this->nodeConf->model instanceof IScalarModel) {
                $value[] = $this->formFactory
                    ->createTransformer($this->nodeConf, $fieldId, withCsrf: false)
                    ->transformSubmittedData($data, $uploadedFiles)
                ;
            }
        }

        return $value;
    }
}
