<?php

declare(strict_types=1);

namespace LM\WebFramework\File;
use LM\WebFramework\Configuration;
use LM\WebFramework\Model\Constraints\IUploadedImageConstraint;

final class FileService
{
    public function __construct(
        private Configuration $configuration,
    ) {
    }

    /**
     * @todo Assume that filenames are one-byte encoded.
     * @todo Assume that filenames are in lowercase.
     * @todo Hard-coded file extensions.
     */
    public function getUploadedImages(bool $includeThumbnails = true): array {
        $listOfFiles = scandir($this->configuration->getPathOfUploadedFiles());

        if (!$includeThumbnails) {
            $listOfFiles = array_filter($listOfFiles, fn ($value) => !str_contains($value, '.medium.') && !str_contains($value, '.small.'));
        }

        return array_filter(
            $listOfFiles,
            fn ($value) => 1 === preg_match('/' . IUploadedImageConstraint::FILENAME_REGEX . '/', $value),
        );
    }
}