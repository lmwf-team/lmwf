<?php

declare(strict_types=1);

namespace LMWF\Form\Conf;

use Closure;
use DomainException;
use InvalidArgumentException;
use LMWF\DataStructures\Slug;
use LMWF\ErrorHandling\Log;
use LMWF\Constraint\Type\ArrayModel;
use LMWF\Constraint\Type\BoolModel;
use LMWF\Constraint\Type\DateTimeModel;
use LMWF\Constraint\Type\ILengthModel;
use LMWF\Constraint\Type\IModel;
use LMWF\Constraint\Type\IntModel;
use LMWF\Constraint\Type\StringModel;
use UnexpectedValueException;

/**
 * @todo Create FormConf class? Inheriting AppObject or using traits?
 */
readonly class FormConfFactory
{
    public const ACCEPT_KN = 'accept';
    public const AUTOCOMPLETE_KN = 'autocomplete';
    public const DEFAULT_KN = 'default';
    public const DEFAULT_SLUG_KN = 'slug';
    public const ID_KN = 'id';
    public const IGNORE_KN = 'ignore';
    public const LABEL_KN = 'label';
    public const REQUIRED_KN = 'required';
    public const TYPE_KN = 'type';
    public const VALUES_KN = 'values';

    /**
     * @param array<string, array<string, mixed>> $formConfParams
     * @return array<string, FormFieldConf>
     */
    public function createConf(ArrayModel $model, array $formConfParams): array
    {
        $formConf = [];
        $processedFieldIds = [];
        $properties = $model->getProperties();
        foreach ($formConfParams as $fieldId => $fieldConfParams) {
            $processedFieldIds[] = $fieldId;
            if (key_exists(self::IGNORE_KN, $fieldConfParams) && true === $fieldConfParams[self::IGNORE_KN]) {
                continue;
            }
            $formConf[$fieldId] = $this->createFormFieldConf($properties[$fieldId] ?? null, $fieldConfParams);
        }
        foreach (array_keys($properties) as $pId) {
            if (false === in_array($pId, $processedFieldIds, strict: true)) {
                throw new InvalidArgumentException("A property of the model ('$pId') wasn't configured for the form.");
            }
        }
        return $formConf;
    }

    /**
     * @param array<string, mixed> $fieldConfParams
     */
    private function createFormFieldConf(?IModel $model, array $fieldConfParams): FormFieldConf
    {
        $defaultFn = key_exists(self::DEFAULT_KN, $fieldConfParams) ? $this->getCallback($fieldConfParams[self::DEFAULT_KN]) : null;
        $isRequired = null === $defaultFn ?
            (
                key_exists(self::REQUIRED_KN, $fieldConfParams) ?
                    $fieldConfParams[self::REQUIRED_KN] :
                    (null === $model ? true : !$model->isNullable())
            ) :
            false;

        $rangeConstraint = null;
        if ($model instanceof ILengthModel and null !== $model->getRangeConstraint()) {
            $rangeConstraint = $model->getRangeConstraint();
        }

        $type = $this->getType($fieldConfParams, $model);

        return new FormFieldConf(
            $model,
            $fieldConfParams[self::LABEL_KN],
            $fieldConfParams[self::AUTOCOMPLETE_KN] ?? null,
            $defaultFn,
            $fieldConfParams[self::ID_KN] ?? null,
            $isRequired,
            $rangeConstraint,
            $type,
            $fieldConfParams[self::VALUES_KN] ?? null,
        );
    }

    /**
     * @param Closure|array{slug: string} $callbackConf
     * @todo Type hint with callable instead of Closure?
     */
    private function getCallback(array|Closure $callbackConf): Closure
    {
        if ($callbackConf instanceof Closure) {
            return $callbackConf;
        }
        $slugCallbackConf = $callbackConf[self::DEFAULT_SLUG_KN];
        return function ($values) use ($slugCallbackConf) {
            return null !== $values[$slugCallbackConf] ? (new Slug($values[$slugCallbackConf], true))->__toString() : null;
        };
    }

    /**
     * @param array<string, mixed> $fieldConfParams
     */
    private function getType(array $fieldConfParams, ?IModel $model): FormFieldType
    {
        if( key_exists(self::TYPE_KN, $fieldConfParams)) {
            return FormFieldType::fromString($fieldConfParams[self::TYPE_KN]);
        } elseif (null !== $model) {
            return $this->getTypeFromModel($model);
        }
        throw new InvalidArgumentException('Both the type and the model cannot be null as the type of the form field must be set.');
    }

    private function getTypeFromModel(IModel $model): FormFieldType
    {
        if ($model instanceof BoolModel) {
            return FormFieldType::Checkbox;
        } elseif ($model instanceof DateTimeModel) {
            return FormFieldType::Date;
        } elseif ($model instanceof IntModel) {
            return FormFieldType::Int;
        } elseif ($model instanceof StringModel and null !== $model->getUploadedImageConstraint()) {
            return FormFieldType::Img;
        } elseif ($model instanceof StringModel) {
            return FormFieldType::Text;
        }
        throw new UnexpectedValueException('Model of type ' . get_class($model) . ' is not recognised.');
    }
}
