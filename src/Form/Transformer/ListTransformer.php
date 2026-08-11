<?php

declare(strict_types=1);

namespace LMWF\Form\Transformer;

use LMWF\Form\Conf\FormFieldConf;
use LMWF\Form\Exceptions\ExtractionException;
use LMWF\Form\FormFactory;
use LMWF\Constraint\Type\IModel;
use LMWF\Constraint\Type\IScalarModel;

final class ListTransformer implements IFormTransformer
{
    public function __construct(
        private FormFieldConf $nodeConf,
        private FormFactory $formFactory,
        private string $name,
    ) {
    }

    /**
     * @return list<mixed>
    */
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
