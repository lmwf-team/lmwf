<?php

declare(strict_types=1);

namespace LMWF\Constraint\Factory;

use LMWF\Constraint\Value\IUploadedImageConstraint;
use LMWF\Constraint\Value\UploadedImageConstraint;
use LMWF\Constraint\Type\StringModel;

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
