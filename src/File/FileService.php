<?php

declare(strict_types=1);

namespace LMWF\File;

use LMWF\Conf\AppConf;
use LMWF\DataStructures\Filename;
use LMWF\DataStructures\Slug;
use UnexpectedValueException;

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

    public function createThumbnails(Filename $filename): void
    {
        $fileContent = file_get_contents("{$this->conf->getPathOfUploadedFiles()}/$filename");
        if (false === $fileContent) {
            throw new UnexpectedValueException("Failed to read the destination image '$filename' to create thumbnail.");
        }

        $originalImg = imagecreatefromstring($fileContent);
        if (false === $originalImg) {
            throw new UnexpectedValueException("Could not create GdImage from content of file '$filename'.");
        }

        list($sizeX, $sizeY) = [imagesx($originalImg), imagesy($originalImg)];

        foreach ($this->conf->thumbnailFormats as $formatId => $format) {
            list($newSizeX, $newSizeY) = $format->scale($sizeX, $sizeY);

            $thumbnailImg = imagecreatetruecolor($newSizeX, $newSizeY);
            if (false === $thumbnailImg) {
                throw new UnexpectedValueException("Could not create empty image with '{$formatId}' thumbnail dimensions for image '{$filename}'.");
            }

            imagecopyresized($thumbnailImg, $originalImg, 0, 0, 0, 0, $newSizeX, $newSizeY, $sizeX, $sizeY);

            $folderName = new Slug($formatId);

            imagewebp(
                $thumbnailImg,
                "{$this->conf->getPathOfUploadedFiles()}/{$folderName}/{$filename}",
                $format->webpQuality,
            );
        }
    }

    public function getAvailablePathForUploadedImg(Filename $destFilename): string
    {
        $destPath = "{$this->conf->getPathOfUploadedFiles()}/$destFilename";

        $i = 0;
        do {
            $randomNumber = random_int(0, self::IMG_RANDOM_NUMBER_MAX * pow(10, $i));
            $destFilename = $destFilename->withBasename("{$destFilename->basename}-{$randomNumber}");
            $destPath = "{$this->conf->getPathOfUploadedFiles()}/$destFilename";
            $i++;
        } while (file_exists($destPath));

        return $destPath;
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
