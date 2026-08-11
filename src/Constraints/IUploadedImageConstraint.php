<?php

namespace LM\WebFramework\Constraints;

interface IUploadedImageConstraint extends IStringConstraint
{
    const FILENAME_MAX_LENGTH = 128;

    const FILENAME_REGEX = '^(([a-z0-9])[-_\.]?)*(?2)+\.(?2)+$';
}