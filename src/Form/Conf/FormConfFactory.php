<?php

declare(strict_types=1);

namespace LM\WebFramework\Form\Conf;

use LM\WebFramework\Model\Type\ArrayModel;

/**
 * @todo Create FormConf class? Inheriting AppObject or using traits?
 */
readonly class FormConfFactory
{
    public const ACCEPT_KN = 'accept';
    public const AUTOCOMPLETE_KN = 'autocomplete';
    public const DEFAULT_KN = 'default';
    public const IGNORE_KN = 'ignore';
    public const LABEL_KN = 'label';
    public const REQUIRED_KN = 'required';
    public const TYPE_KN = 'type';
    public const VALUES_KN = 'values';

    public function createConf(ArrayModel $model, array $fieldConfs): array
    {
        $formConf = [];
        foreach ($model->getProperties() as $pId => $property) {
            if (key_exists(self::IGNORE_KN, $fieldConfs[$pId]) && true === $fieldConfs[$pId][self::IGNORE_KN]) {
                continue;
            }
            $formConf[$pId] = new FormFieldConf(
                $property,
                $fieldConfs[$pId][self::LABEL_KN],
                $fieldConfs[$pId][self::AUTOCOMPLETE_KN] ?? null,
                $fieldConfs[$pId][self::DEFAULT_KN] ?? null,
                $fieldConfs[$pId][self::REQUIRED_KN] ?? null,
                $fieldConfs[$pId][self::TYPE_KN] ?? null,
                $fieldConfs[$pId][self::VALUES_KN] ?? null,
            );
        }
        return $formConf;
    }
}
