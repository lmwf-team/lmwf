<?php

declare(strict_types=1);

namespace LM\WebFramework\Model\Constraints;

interface IUploadedImageConstraint extends IStringConstraint
{
    // @todo Use enum?
    // @todo Create model for images.
    const FILE_TOO_BIG = "ERROR_FILE_TOO_BIG";
    const MISC_ERROR = "MISC_ERROR";

    const FILENAME_MAX_LENGTH = 128;
    const FILENAME_REGEX = '^(([a-z0-9])[-_\.]?)*(?2)+\.(?2)+$';
}