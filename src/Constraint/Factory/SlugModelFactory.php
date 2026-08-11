<?php

declare(strict_types=1);

namespace LM\WebFramework\Constraint\Factory;

use LM\WebFramework\Constraint\Type\StringModel;
use LM\WebFramework\DataStructures\Slug;

class SlugModelFactory
{
    public function getSlugModel(bool $isNullable = false): StringModel
    {
        return new StringModel(
            lowerLimit: Slug::SLUG_MIN_LENGTH,
            upperLimit: Slug::SLUG_MAX_LENGTH,
            regex: Slug::SLUG_REGEX,
            isNullable: $isNullable,
        );
    }
}
