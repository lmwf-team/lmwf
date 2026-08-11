<?php

declare(strict_types=1);

namespace LMWF\Form;

use DomainException;
use InvalidArgumentException;
use LMWF\Conf\AppConf;
use LMWF\Form\Conf\FormConfFactory;
use LMWF\Form\Conf\FormFieldConf;
use LMWF\Form\Conf\FormFieldType;
use LMWF\Form\Transformer\ArrayTransformer;
use LMWF\Form\Transformer\CheckboxTransformer;
use LMWF\Form\Transformer\CsrfTransformer;
use LMWF\Form\Transformer\DateTimeTransformer;
use LMWF\Form\Transformer\ImgFileTransformer;
use LMWF\Form\Transformer\IFormTransformer;
use LMWF\Form\Transformer\IntTransformer;
use LMWF\Form\Transformer\ListTransformer;
use LMWF\Form\Transformer\StringTransformer;
use LMWF\Constraint\Type\ArrayModel;
use LMWF\Constraint\Type\BoolModel;
use LMWF\Constraint\Type\DateTimeModel;
use LMWF\Constraint\Type\EntityListModel;
use LMWF\Constraint\Type\ForeignEntityModel;
use LMWF\Constraint\Type\IModel;
use LMWF\Constraint\Type\IntModel;
use LMWF\Constraint\Type\ListModel;
use LMWF\Constraint\Type\StringModel;
use LMWF\File\FileService;

/**
 * Creates a form transformer from a model.
 */
final class FormFactory
{
    public const CSRF_FORM_ELEMENT_NAME = '_csrf';

    public function __construct(
        private CsrfTransformer $csrfTransformer,
        private FileService $fileService,
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
            return new ImgFileTransformer($this->fileService, $name);
        } elseif (FormFieldType::Checkbox === $fieldConf->type) {
            return new CheckboxTransformer($name);
        } elseif (FormFieldType::Date === $fieldConf->type) {
            return new DateTimeTransformer($name);
        } elseif (FormFieldType::Int === $fieldConf->type) {
            return new IntTransformer($name);
        }
    }

    /**
     * @param array<string, FormFieldConf> $formConf
     */
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
            if (null !== $fieldConf->default) {
                $fieldDefaults[$fieldName] = $fieldConf->default;
            }
        }
        return new ArrayTransformer(
            $fieldTransformers,
            $withCsrf ? $this->csrfTransformer : null,
            $fieldDefaults,
            $name,
        );
    }
}
