<?php

declare(strict_types=1);

namespace LM\WebFramework\Model\Factory;

use LM\WebFramework\Model\Type\StringModel;

class UrlModelFactory
{
    const URL_MAX_LENGTH = 255;

    const URL_MIN_LENGTH = 1;

    /**
     * @link https://stackoverflow.com/questions/3809401/what-is-a-good-regular-expression-to-match-a-url
     */
    const URL_REGEX = 'https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_\+.~#?&//=]*)';

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