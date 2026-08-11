<?php

declare(strict_types=1);

namespace LM\WebFramework\Model\Factory;

use LM\WebFramework\Model\Constraints\IUploadedImageConstraint;
use LM\WebFramework\Model\Constraints\UploadedImageConstraint;
use LM\WebFramework\Model\Type\StringModel;

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
