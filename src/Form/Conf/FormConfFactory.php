<?php

declare(strict_types=1);

namespace LM\WebFramework\Form\Conf;

use LM\WebFramework\Model\Type\ArrayModel;
use LM\WebFramework\Model\Type\IModel;
use LM\WebFramework\DataStructures\Slug;

/**
 * @todo Create FormConf class? Inheriting AppObject or using traits?
 */
readonly class FormConfFactory
{
    public const ACCEPT_KN = 'accept';
    public const AUTOCOMPLETE_KN = 'autocomplete';
    public const DEFAULT_KN = 'default';
    public const DERIVE_KN = 'derive';
    public const DERIVE_SLUG_KN = 'slug';
    public const IGNORE_KN = 'ignore';
    public const LABEL_KN = 'label';
    public const REQUIRED_KN = 'required';
    public const TYPE_KN = 'type';
    public const VALUES_KN = 'values';

    public function createConf(ArrayModel $model, array $formConfParams): array
    {
        $formConf = [];
        foreach ($model->getProperties() as $pId => $property) {
            if (key_exists(self::IGNORE_KN, $formConfParams[$pId]) && true === $formConfParams[$pId][self::IGNORE_KN]) {
                continue;
            }
            $formConf[$pId] = $this->createFormFieldConf($property, $formConfParams[$pId]);
        }
        return $formConf;
    }

    private function createFormFieldConf(IModel $model, array $fieldConfParams): FormFieldConf
    {
        $defaultFn = $fieldConfParams[self::DEFAULT_KN] ?? null;
        $isRequired = $fieldConfParams[self::REQUIRED_KN] ?? null;
        if (key_exists(self::DERIVE_KN, $fieldConfParams)) {
            if (key_exists(self::DERIVE_SLUG_KN, $fieldConfParams[self::DERIVE_KN])) {
                $sourceId = $fieldConfParams[self::DERIVE_KN][self::DERIVE_SLUG_KN];
                $defaultFn = fn ($values) => null !== $values[$sourceId] ? (new Slug($values[$sourceId], true))->__toString() : null;
                $isRequired = false;
            }
        }
        return new FormFieldConf(
            $model,
            $fieldConfParams[self::LABEL_KN],
            $fieldConfParams[self::AUTOCOMPLETE_KN] ?? null,
            $defaultFn,
            $isRequired,
            $fieldConfParams[self::TYPE_KN] ?? null,
            $fieldConfParams[self::VALUES_KN] ?? null,
        );
    }
}
