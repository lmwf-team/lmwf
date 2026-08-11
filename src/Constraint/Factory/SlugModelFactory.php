<?php

declare(strict_types=1);

namespace LMWF\Constraint\Factory;

use LMWF\Constraint\Type\StringModel;
use LMWF\DataStructures\Slug;

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
