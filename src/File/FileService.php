<?php

declare(strict_types=1);

namespace LMWF\File;

use LMWF\Conf\AppConf;
use LMWF\DataStructures\Filename;

final class FileService
{
    const int IMG_RANDOM_NUMBER_MAX = 9999;

    /**
     * @var list<string>
     */
    const array IMG_SUPPORTED_MIME_TYPES = [
        'image/webp',
    ];

    public function __construct(
        private AppConf $conf,
    ) {
    }

    public function getAvailableImgFilename(Filename $destFilename): Filename
    {
        $mutFilename = $destFilename;
        $i = 0;
        do {
            $randomNumber = random_int(0, self::IMG_RANDOM_NUMBER_MAX * pow(10, $i));
            $mutFilename = $mutFilename->withBasename("{$mutFilename->basename}-{$randomNumber}");
            $i++;
        } while (file_exists("{$this->conf->getPathOfUploadedFiles()}/$mutFilename"));

        return $mutFilename;
    }

    /**
     * @todo Assume that filenames are one-byte encoded.
     * @todo Assume that filenames are in lowercase.
     * @todo Hard-coded file extensions.
     *
     * @return list<string> Filenames of all images in the uploaded files
     * folder (subfolders not included).
     */
    public function getUploadedImages(): array
    {
        $resourcesDiskPath = $this->conf->getPathOfUploadedFiles();
        $filenames = scandir($resourcesDiskPath);

        return array_filter(
            $filenames,
            fn ($filename) => in_array(mime_content_type("$resourcesDiskPath/$filename"), self::IMG_SUPPORTED_MIME_TYPES, strict: true)
        ) |> array_values(...);
    }
}
