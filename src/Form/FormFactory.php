<?php

declare(strict_types=1);

namespace LM\WebFramework\Form;

use DomainException;
use InvalidArgumentException;
use LM\WebFramework\Configuration;
use LM\WebFramework\Form\Transformer\ArrayTransformer;
use LM\WebFramework\Form\Transformer\CheckboxTransformer;
use LM\WebFramework\Form\Transformer\CsrfTransformer;
use LM\WebFramework\Form\Transformer\DateTimeTransformer;
use LM\WebFramework\Form\Transformer\FileTransformer;
use LM\WebFramework\Form\Transformer\IFormTransformer;
use LM\WebFramework\Form\Transformer\IntTransformer;
use LM\WebFramework\Form\Transformer\JsonTransformer;
use LM\WebFramework\Form\Transformer\ListTransformer;
use LM\WebFramework\Form\Transformer\StringTransformer;
use LM\WebFramework\Model\Type\ArrayModel;
use LM\WebFramework\Model\Type\BoolModel;
use LM\WebFramework\Model\Type\DateTimeModel;
use LM\WebFramework\Model\Type\EntityListModel;
use LM\WebFramework\Model\Type\ForeignEntityModel;
use LM\WebFramework\Model\Type\IModel;
use LM\WebFramework\Model\Type\IntModel;
use LM\WebFramework\Model\Type\JsonModel;
use LM\WebFramework\Model\Type\ListModel;
use LM\WebFramework\Model\Type\StringModel;

/**
 * Creates a form transformer from a model.
 */
final class FormFactory
{
    public const CSRF_FORM_ELEMENT_NAME = '_csrf';

    public function __construct(
        private Configuration $config,
        private CsrfTransformer $csrfTransformer,
    ) {
    }

    public function createForm(ArrayModel $model, array $config = []): ArrayTransformer
    {
        return $this->createTransformer($model, $config, null, true);
    }

    public function createTransformer(IModel $model, array $config = [], ?string $name = null, bool $csrf = false): IFormTransformer
    {
        if ($model instanceof ForeignEntityModel) {
            return $this->createTransformer($model->getEntityModel(), $config, $name, $csrf);
        } elseif ($model instanceof ArrayModel) {
            $formElements = [];
            $defaultCallbacks = [];
            foreach ($model->getProperties() as $key => $property) {
                $propConfig = $config[$key] ?? [];
                $propConfig['ignore'] = $config[$key]['ignore'] ?? false;
                if (!$propConfig['ignore']) {
                    $formElements[$key] = $this->createTransformer($property, $config[$key] ?? [], $key);
                }
            }
            return new ArrayTransformer($formElements, $csrf ? $this->csrfTransformer : null, $name, $defaultCallbacks);
        }
        if (null === $name) {
            throw new InvalidArgumentException('A name must be provided for non-array transformers.');
        }
        if ($model instanceof ListModel || $model instanceof EntityListModel) {
            return new ListTransformer($model->getItemModel(), $config, $this, $name);
        }
        if ($model instanceof StringModel) {
            if (null !== $model->getUploadedImageConstraint()) {
                return new FileTransformer($this->config->getPathOfUploadedFiles(), $name);
            }
            return new StringTransformer($name);
        }
        if ($model instanceof IntModel) {
            return new IntTransformer($name);
        }
        if ($model instanceof DateTimeModel) {
            return new DateTimeTransformer($name);
        }
        if ($model instanceof BoolModel) {
            return new CheckboxTransformer($name);
        }
        if ($model instanceof JsonModel) {
            return new JsonTransformer($name);
        }

        throw new DomainException('No transformer found for ' . get_class($model) . '.');
    }
}
