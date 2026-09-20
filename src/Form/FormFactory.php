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
use LMWF\Form\Transformer\StringEnumTransformer;
use StringBackedEnum;
use UnexpectedValueException;

/**
 * Creates transformers for converting data received from a form submission into
 * app data, based on its form configuration: a FormFieldConf or a dict of
 * FormFieldConf (array<string, FormFieldConf>).
 *
 * Why not transforming it from a model directly? This is because the model is
 * used to validate the data.
 *
 * @phpstan-import-type fieldconfparams from FormConfFactory
 */
final class FormFactory
{
    public const CSRF_FORM_ELEMENT_NAME = '_csrf';

    public function __construct(
        private AppConf $conf,
        private CsrfTransformer $csrfTransformer,
        private FileService $fileService,
        private FormConfFactory $formConfFactory,
    ) {
    }

    /**
     * @param array<string, fieldconfparams> $formConfParams
     */
    public function createForm(ArrayModel $model, array $formConfParams = []): ArrayTransformer
    {
        $formConf = $this->formConfFactory->createConf($model, $formConfParams);
        return $this->createFormTransformer($formConf, null);
    }

    public function createFieldTransformer(
        FormFieldConf $fieldConf,
        ?string $name = null,
    ): IFormTransformer {
        if (null === $name) {
            throw new InvalidArgumentException('A name must be provided for non-array transformers.');
        }

        if (null !== $fieldConf->stringEnumModel) {
            return new StringEnumTransformer($fieldConf->stringEnumModel, $name);
        }
        if (in_array($fieldConf->type, [FormFieldType::Text, FormFieldType::Textarea, FormFieldType::Pwd], strict: true)) {
            return new StringTransformer($name);
        }

        return match ($fieldConf->type) {
            FormFieldType::Img => new ImgFileTransformer($this->conf, $this->fileService, $name),
            FormFieldType::Checkbox => new CheckboxTransformer($name),
            FormFieldType::Date => new DateTimeTransformer($name),
            FormFieldType::Int => new IntTransformer($name),
            // PHPStan should prevent this.
            // default => throw new UnexpectedValueException('Received unknown form field configuration type: ' . $fieldConf->type->value),
        };
    }

    /**
     * @param array<string, FormFieldConf> $formConf
     */
    public function createFormTransformer(
        array $formConf,
        ?string $name = null,
        bool $withCsrf = true,
    ): ArrayTransformer {
        $fieldTransformers = [];
        $fieldDefaults = [];
        foreach ($formConf as $fieldName => $fieldConf) {
            $fieldTransformers[$fieldName] = $this->createFieldTransformer(
                $fieldConf,
                $fieldName,
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
