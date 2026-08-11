<?php

declare(strict_types=1);

namespace LM\WebFramework\Constraint\Factory;

use LM\WebFramework\Constraint\Type\StringModel;

final class VarcharModelFactory
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
