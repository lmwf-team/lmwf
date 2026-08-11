<?php

declare(strict_types=1);

namespace LM\WebFramework\Model\Factory;

use LM\WebFramework\Model\Type\StringModel;

class UploadedImageModelFactory
{
    const FILENAME_MAX_LENGTH = 128;

    const FILENAME_REGEX = '^(([a-z0-9])[-_\.]?)*(?2)+\.(?2)+$';

    public function createModel(bool $isNullable = false): StringModel
    {
        return new StringModel(
            upperLimit: self::FILENAME_MAX_LENGTH,
            regex: self::FILENAME_REGEX,
            isNullable: $isNullable,
        );
    }
}