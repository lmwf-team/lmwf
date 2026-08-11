<?php

declare(strict_types=1);

namespace LM\WebFramework\Model\Constraints;

interface IUploadedImageConstraint extends IStringConstraint
{
    public const FILE_TOO_BIG_ERROR = "ERROR_FILE_TOO_BIG";

    public const FILENAME_MAX_LENGTH = 128;

    public const FILENAME_REGEX = '^(([a-z0-9])[-_\.]?)*(?2)+\.(?2)+$';

    public const MISC_ERROR = "MISC_ERROR";
}
