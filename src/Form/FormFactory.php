<?php

declare(strict_types=1);

namespace LM\WebFramework\Form;

use DomainException;
use InvalidArgumentException;
use LM\WebFramework\Configuration\Configuration;
use LM\WebFramework\Form\Conf\FormConfFactory;
use LM\WebFramework\Form\Conf\FormFieldConf;
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
        private Configuration $conf,
        private CsrfTransformer $csrfTransformer,
        private FormConfFactory $formConfFactory,
    ) {
    }

    public function createForm(ArrayModel $model, array $fieldConfs = []): ArrayTransformer
    {
        $formConf = $this->formConfFactory->createConf($model, $fieldConfs);
        return $this->createFormTransformer($formConf, null, true);
    }

    /**
     * @todo To delete probably.
     */
    public function createTransformer(
        array|FormFieldConf $conf,
        ?string $name = null,
        bool $withCsrf = false,
    ): IFormTransformer {
        if ($conf instanceof FormFieldConf) {
            return $this->createFieldTransformer($conf, $name);
        }
        return $this->createFormTransformer($conf, $name, $withCsrf);
    }

    public function createFieldTransformer(
        FormFieldConf $conf,
        ?string $name = null,
    ): IFormTransformer {
        if (null === $name) {
            throw new InvalidArgumentException('A name must be provided for non-array transformers.');
        }
        if ($conf->model instanceof ListModel || $conf->model instanceof EntityListModel) {
            return new ListTransformer($conf->model->getItemModel(), $conf, $this, $name);
        }
        if ($conf->model instanceof StringModel) {
            if (null !== $conf->model->getUploadedImageConstraint()) {
                return new FileTransformer($this->conf->getPathOfUploadedFiles(), $name);
            }
            return new StringTransformer($name);
        }
        if ($conf->model instanceof IntModel) {
            return new IntTransformer($name);
        }
        if ($conf->model instanceof DateTimeModel) {
            return new DateTimeTransformer($name);
        }
        if ($conf->model instanceof BoolModel) {
            return new CheckboxTransformer($name);
        }
        if ($conf->model instanceof JsonModel) {
            return new JsonTransformer($name);
        }

        throw new DomainException('No transformer found for ' . get_class($conf->model) . '.');
    }

    public function createFormTransformer(
        array $formConf,
        ?string $name = null,
        bool $withCsrf = false,
    ): ArrayTransformer {
        $fieldTransformers = [];
        foreach ($formConf as $key => $fieldConf) {
            $fieldTransformers[$key] = $this->createTransformer(
                $fieldConf,
                $key,
                false,
            );
        }
        return new ArrayTransformer(
            $fieldTransformers,
            $withCsrf ? $this->csrfTransformer : null,
            $name,
            [],
        );
    }
}
