<?php

declare(strict_types=1);

namespace LM\WebFramework\Constraint\Factory;

use LM\WebFramework\Constraint\Value\IUploadedImageConstraint;
use LM\WebFramework\Constraint\Value\UploadedImageConstraint;
use LM\WebFramework\Constraint\Type\StringModel;

class UploadedImageModelFactory
{
    public function getModel(bool $isNullable = false): StringModel
    {
        return new StringModel(
            upperLimit: IUploadedImageConstraint::FILENAME_MAX_LENGTH,
            regex: IUploadedImageConstraint::FILENAME_REGEX,
            isNullable: $isNullable,
            uploadedImageConstraint: new UploadedImageConstraint(),
        );
    }
}
