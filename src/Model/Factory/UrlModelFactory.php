<?php

declare(strict_types=1);

namespace LM\WebFramework\Model\Factory;

use LM\WebFramework\Model\Type\StringModel;

class UrlModelFactory
{
    public const URL_MAX_LENGTH = 255;

    public const URL_MIN_LENGTH = 1;

    public const URL_REGEX = '^(https?:\/\/)?\w([\w.-]*\w)?\.\w+(\/.*)?';

    public function getUrlModel(bool $isNullable = false): StringModel
    {
        return new StringModel(
            lowerLimit: self::URL_MIN_LENGTH,
            upperLimit: self::URL_MAX_LENGTH,
            regex: self::URL_REGEX,
            isNullable: $isNullable,
        );
    }
}
