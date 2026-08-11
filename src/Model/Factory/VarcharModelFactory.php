<?php

declare(strict_types=1);

namespace LM\WebFramework\Model\Factory;

use LM\WebFramework\Model\Type\StringModel;

class VarcharModelFactory
{
    public const MAX_LENGTH = 255;

    public function getSlugModel(bool $isNullable = false): StringModel
    {
        return new StringModel(
            upperLimit: self::MAX_LENGTH,
            isNullable: $isNullable,
        );
    }
}
