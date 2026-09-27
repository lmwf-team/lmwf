<?php

declare(strict_types=1);

namespace LMWF\File;

use LMWF\Conf\AppConf;
use LMWF\DataStructures\Filename;
use LMWF\DataStructures\Slug;
use UnexpectedValueException;
use LMWF\DataStructures\ImgFormat;

final readonly class ThumbnailWriter
{
    /**
     * @param array<string, ImgFormat> $formats Formats sorted by size.
     */
    public function __construct(
        private string $uploadDirAbsDiskPath,
        private array $formats,
    ) {
    }

    public function createThumbnails(Filename $filename): void
    {
        $fileContent = file_get_contents("{$this->uploadDirAbsDiskPath}/$filename");
        if (false === $fileContent) {
            throw new UnexpectedValueException("Failed to read the destination image '$filename' to create thumbnail.");
        }

        $originalImg = imagecreatefromstring($fileContent);
        if (false === $originalImg) {
            throw new UnexpectedValueException("Could not create GdImage from content of file '$filename'.");
        }

        list($sizeX, $sizeY) = [imagesx($originalImg), imagesy($originalImg)];

        foreach ($this->formats as $formatId => $format) {
            if ($sizeX <= $format->minSizeX || $sizeY <= $format->minSizeY) {
                return;
            }
            list($newSizeX, $newSizeY) = $format->scale($sizeX, $sizeY);

            $thumbnailImg = imagecreatetruecolor($newSizeX, $newSizeY);
            if (false === $thumbnailImg) {
                throw new UnexpectedValueException("Could not create empty image with '{$formatId}' thumbnail dimensions for image '{$filename}'.");
            }

            imagecopyresized($thumbnailImg, $originalImg, 0, 0, 0, 0, $newSizeX, $newSizeY, $sizeX, $sizeY);

            $folderName = new Slug($formatId);

            imagewebp(
                $thumbnailImg,
                "{$this->uploadDirAbsDiskPath}/{$folderName}/{$filename}",
                $format->webpQuality,
            );
        }
    }
}
