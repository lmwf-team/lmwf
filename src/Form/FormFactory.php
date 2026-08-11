<?php

declare(strict_types=1);

namespace LM\WebFramework\Form;

use DomainException;
use InvalidArgumentException;
use LM\WebFramework\Conf\AppConf;
use LM\WebFramework\Form\Conf\FormConfFactory;
use LM\WebFramework\Form\Conf\FormFieldConf;
use LM\WebFramework\Form\Conf\FormFieldType;
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
use LM\WebFramework\Constraint\Type\ArrayModel;
use LM\WebFramework\Constraint\Type\BoolModel;
use LM\WebFramework\Constraint\Type\DateTimeModel;
use LM\WebFramework\Constraint\Type\EntityListModel;
use LM\WebFramework\Constraint\Type\ForeignEntityModel;
use LM\WebFramework\Constraint\Type\IModel;
use LM\WebFramework\Constraint\Type\IntModel;
use LM\WebFramework\Constraint\Type\ListModel;
use LM\WebFramework\Constraint\Type\StringModel;

/**
 * Creates a form transformer from a model.
 */
final class FormFactory
{
    public const CSRF_FORM_ELEMENT_NAME = '_csrf';

    public function __construct(
        private AppConf $conf,
        private CsrfTransformer $csrfTransformer,
        private FormConfFactory $formConfFactory,
    ) {
    }

    /**
     * @param array<string, array<string, mixed>> $formConfParams
     */
    public function createForm(ArrayModel $model, array $formConfParams = []): ArrayTransformer
    {
        $formConf = $this->formConfFactory->createConf($model, $formConfParams);
        return $this->createFormTransformer($formConf, null, true);
    }

    /**
     * @todo To delete?
     * @param array<string, FormFieldConf>|FormFieldConf $conf
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
        FormFieldConf $fieldConf,
        ?string $name = null,
    ): IFormTransformer {
        if (null === $name) {
            throw new InvalidArgumentException('A name must be provided for non-array transformers.');
        }
        // @todo Add List, EntityList, and Json to FormFieldType
        if ($fieldConf->model instanceof ListModel || $fieldConf->model instanceof EntityListModel) {
            return new ListTransformer($fieldConf, $this, $name);
        } elseif (in_array($fieldConf->type, [FormFieldType::Text, FormFieldType::Textarea, FormFieldType::Pwd], strict: true)) {
            return new StringTransformer($name);
        } elseif (FormFieldType::Img === $fieldConf->type) {
            return new FileTransformer($this->conf->getPathOfUploadedFiles(), $name);
        } elseif (FormFieldType::Checkbox === $fieldConf->type) {
            return new CheckboxTransformer($name);
        } elseif (FormFieldType::Date === $fieldConf->type) {
            return new DateTimeTransformer($name);
        } elseif (FormFieldType::Int === $fieldConf->type) {
            return new IntTransformer($name);
        }
    }

    public function createFormTransformer(
        array $formConf,
        ?string $name = null,
        bool $withCsrf = false,
    ): ArrayTransformer {
        $fieldTransformers = [];
        $fieldDefaults = [];
        foreach ($formConf as $fieldName => $fieldConf) {
            $fieldTransformers[$fieldName] = $this->createTransformer(
                $fieldConf,
                $fieldName,
                false,
            );
            $fieldDefaults[$fieldName] = $fieldConf->default;
        }
        return new ArrayTransformer(
            $fieldTransformers,
            $withCsrf ? $this->csrfTransformer : null,
            $fieldDefaults,
            $name,
        );
    }
}
