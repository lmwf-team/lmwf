<?php

declare(strict_types=1);

namespace LM\WebFramework\Form;

use DomainException;
use InvalidArgumentException;
use LM\WebFramework\Configuration;
use LM\WebFramework\Constraints\IUploadedImageConstraint;
use LM\WebFramework\Form\Transformer\ArrayTransformer;
use LM\WebFramework\Form\Transformer\CheckboxTransformer;
use LM\WebFramework\Form\Transformer\CsrfTransformer;
use LM\WebFramework\Form\Transformer\DateTimeTransformer;
use LM\WebFramework\Form\Transformer\FileTransformer;
use LM\WebFramework\Form\Transformer\IFormTransformer;
use LM\WebFramework\Form\Transformer\ListTransformer;
use LM\WebFramework\Form\Transformer\StringTransformer;
use LM\WebFramework\Model\IModel;

/**
 * Automatically creates a Form object from a model definition.
 */
class FormFactory
{
    const CSRF_FORM_ELEMENT_NAME = '_csrf';

    public function __construct(
        private Configuration $config,
        private CsrfTransformer $csrfTransformer,
    ) {
    }

    public function createForm(IModel $model, array $config = []): ArrayTransformer {
        if (null === $model->getArrayDefinition()) {
            throw new InvalidArgumentException('Model must possess an array definition.');
        }
        return $this->createTransformer($model, $config, null, true);
    }

    public function createTransformer(IModel $model, array $config = [], ?string $name = null, bool $csrf = false): IFormTransformer {
        if (null !== $model->getArrayDefinition()) {
            $formElements = [];
            $defaultCallbacks = [];
            foreach ($model->getArrayDefinition() as $key => $property) {
                if (!isset($config[$key]['ignore']) || false === $config[$key]['ignore']) {
                    $formElements[$key] = $this->createTransformer($property, $config[$key] ?? [], $key);
                }
                if (isset($config[$key]['default'])) {
                    $defaultCallbacks[$key] = $config[$key]['default'];
                }
            }
            return new ArrayTransformer($formElements, $csrf ? $this->csrfTransformer : null, $name, $defaultCallbacks);

        }
        if (null === $name) {
            throw new InvalidArgumentException('A name must be provided for non-array transformers.');
        }
        if (null !== $model->getListNodeModel()) {
            return new ListTransformer($model->getListNodeModel(), $config, $this, $name);
        }
        if (null !== $model->getStringConstraints() || null !== $model->getIntegerConstraints()) {
            if (null !== $model->getStringConstraints()) {
                foreach ($model->getStringConstraints() as $c) {
                    if ($c instanceof IUploadedImageConstraint) {
                        return new FileTransformer($this->config->getPathOfUploadedFiles(), $name);
                    }
                }
            }
            return new StringTransformer($name);
        }
        if (null !== $model->getDateTimeConstraints()) {
            return new DateTimeTransformer($name);
        }
        if ($model->isBool()) {
            return new CheckboxTransformer($name);
        }

        throw new DomainException('No transformer found for ' . get_class($model) . '.');
    }
}