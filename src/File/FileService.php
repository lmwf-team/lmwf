<?php

namespace LM\WebFramework\File;
use LM\WebFramework\Constraints\IUploadedImageConstraint;

class FileService
{
    /**
     * @todo Assume that filenames are one-byte encoded.
     * @todo Assume that filenames are in lowercase.
     * @todo Hard-coded file extensions.
     */
    public function getUploadedImages(bool $includeThumbnails = true): array {
        $listOfFiles = scandir(dirname(__FILE__) . '/../../../public/uploaded/');

        if (!$includeThumbnails) {
            $listOfFiles = array_filter($listOfFiles, fn ($value) => !str_contains($value, '.medium.') && !str_contains($value, '.small.'));
        }

        return array_filter(
            $listOfFiles,
            fn ($value) => 1 === preg_match('/' . IUploadedImageConstraint::FILENAME_REGEX . '/', $value),
        );
    }
}