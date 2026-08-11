<?php

declare(strict_types=1);

namespace LM\WebFramework\Model\Factory;

use LM\WebFramework\Model\Type\StringModel;

class SlugModelFactory
{
    public const SLUG_MAX_LENGTH = 255;

    public const SLUG_MIN_LENGTH = 1;

    public const SLUG_REGEX = '^(([a-z0-9])-?)*(?2)+$';

    public function getSlugModel(bool $isNullable = false): StringModel
    {
        return new StringModel(
            lowerLimit: self::SLUG_MIN_LENGTH,
            upperLimit: self::SLUG_MAX_LENGTH,
            regex: self::SLUG_REGEX,
            isNullable: $isNullable,
        );
    }
}
