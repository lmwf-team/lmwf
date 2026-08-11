<?php

declare(strict_types=1);

namespace LMWF\Form\Transformer;

use LMWF\DataStructures\Filename;
use LMWF\DataStructures\Slug;
use LMWF\ErrorHandling\Log;
use LMWF\Constraint\Value\IUploadedImageConstraint;
use LMWF\DataStructures\ImgFormat;
use LMWF\File\FileService;
use Psr\Http\Message\UploadedFileInterface;
use UnexpectedValueException;

final readonly class ImgFileTransformer implements IFormTransformer
{
    public const PREVIOUS_SUFFIX = '_previous';

    public function __construct(
        private FileService $fileService,
        private string $name,
        private bool $createThumbnails = true,
    ) {
    }

    /**
     * Save and convert the submitted file and return its public filenames, or
     * return the previously submitted file public filenames.
     *
     * @return null|list<string>|string
     */
    #[\Override]
    public function transformSubmittedData(array $parsedPayload, array $uploadedFiles): null|array|string
    {
        // Apparently, the key always exists even if no file was submitted, so I commented this.
        // if (!key_exists($this->name, $uploadedFiles)) {
        //     return $this->extractPreviousFilename($parsedPayload);
        // }

        $uploaded = $uploadedFiles[$this->name];

        if (is_array($uploaded)) {
            $filenames = [];
            foreach ($uploaded as $img) {
                $filenames[] = $this->saveUploadedImage($img);
            }
            return $filenames;
        } else {
            return $this->saveUploadedImage($uploaded) ?? $this->extractPreviousFilename($parsedPayload);
        }
    }

    /**
     * @todo Handle multiple filenames.
     *
     * @param array<string, mixed> $parsedPayload
     */
    private function extractPreviousFilename(array $parsedPayload): ?string
    {
        if (key_exists($this->name . self::PREVIOUS_SUFFIX, $parsedPayload)) {
            Log::info("Extracting previously uploaded file for {$this->name}.");
            $filename = Filename::fromString($parsedPayload[$this->name . self::PREVIOUS_SUFFIX]);
            return $filename->getFilename();
        } else {
            return null;
        }
    }

    private function saveUploadedImage(UploadedFileInterface $uploadedFile): null|string
    {
        switch ($uploadedFile->getError()) {
            case UPLOAD_ERR_OK:
                $destFilename = Filename::fromString($uploadedFile->getClientFilename(), transform: true)->withExt('webp');

                $destinationPath = $this->fileService->getAvailablePathForUploadedImg($destFilename);

                $streamGdImg = imagecreatefromstring($uploadedFile->getStream()->getContents());
                if (false === $streamGdImg) {
                    throw new UnexpectedValueException("Could not create GdImage from uploaded file with client name '{$uploadedFile->getClientFilename()}' and type '{$uploadedFile->getClientMediaType()}'.");
                }
                imagewebp($streamGdImg, $destinationPath, ImgFormat::WEBP_QUALITY_HIGH);


                if ($this->createThumbnails) {
                    $this->fileService->createThumbnails($destFilename);
                }

                return $destFilename->getFilename();

            case UPLOAD_ERR_FORM_SIZE:
            case UPLOAD_ERR_INI_SIZE:
                return IUploadedImageConstraint::FILE_TOO_BIG_ERROR;

            case UPLOAD_ERR_NO_FILE:
                return null;

            case UPLOAD_ERR_CANT_WRITE:
            case UPLOAD_ERR_EXTENSION:
            case UPLOAD_ERR_NO_TMP_DIR:
            case UPLOAD_ERR_PARTIAL:
                throw new UnexpectedValueException("Got an unexpected error (code is {$uploadedFile->getError()}) when trying to process uploaded file.");

            default:
                throw new UnexpectedValueException("Got an unknown error (code is {$uploadedFile->getError()}) when trying to process uploaded file.");
        }
    }
}
