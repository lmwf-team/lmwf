<?php

declare(strict_types=1);

namespace LM\WebFramework\Form\Conf;

use Closure;
use DomainException;
use InvalidArgumentException;
use LM\WebFramework\DataStructures\Slug;
use LM\WebFramework\ErrorHandling\Log;
use LM\WebFramework\Model\Type\ArrayModel;
use LM\WebFramework\Model\Type\BoolModel;
use LM\WebFramework\Model\Type\DateTimeModel;
use LM\WebFramework\Model\Type\ILengthModel;
use LM\WebFramework\Model\Type\IModel;
use LM\WebFramework\Model\Type\IntModel;
use LM\WebFramework\Model\Type\StringModel;
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

    private function createFormFieldConf(?IModel $model, array $fieldConfParams): FormFieldConf
    {
        $defaultFn = key_exists(self::DEFAULT_KN, $fieldConfParams) ? $this->getCallback($fieldConfParams[self::DEFAULT_KN]) : null;
        $isRequired = null !== $defaultFn ? false : $fieldConfParams[self::REQUIRED_KN] ?? !$model?->isNullable() ?? true;

        $rangeConstraint = null;
        if ($model instanceof ILengthModel and null !== $model->getRangeConstraint()) {
            $rangeConstraint = $model->getRangeConstraint();
        }
        
        $type = key_exists(self::TYPE_KN, $fieldConfParams) ? FormFieldType::fromString($fieldConfParams[self::TYPE_KN]) : $this->getTypeFromModel($model);

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

    private function getTypeFromModel(IModel $model): FormFieldType
    {
        if ($model instanceof BoolModel) {
            return FormFieldType::Checkbox;
        } elseif ($model instanceof DateTimeModel) {
            return FormFieldType::Date;
        } elseif ($model instanceof IntModel) {
            return FormFieldType::Int;
        } elseif ($model instanceof StringModel and $model->getUploadedImageConstraint()) {
            return FormFieldType::Img;
        } elseif ($model instanceof StringModel) {
            return FormFieldType::Text;
        }
        throw new UnexpectedValueException('Model of type ' . get_class($model) . ' is not recognised.');
    }
}
