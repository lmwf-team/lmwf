<?php

declare(strict_types=1);

namespace LM\WebFramework\Model\Constraints;

interface IUploadedImageConstraint extends IStringConstraint
{
    const FILE_TOO_BIG_ERROR = "ERROR_FILE_TOO_BIG";

    const FILENAME_MAX_LENGTH = 128;

    const FILENAME_REGEX = '^(([a-z0-9])[-_\.]?)*(?2)+\.(?2)+$';
    
    const MISC_ERROR = "MISC_ERROR";
}