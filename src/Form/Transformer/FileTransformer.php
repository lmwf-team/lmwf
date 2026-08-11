<?php

declare(strict_types=1);

namespace LM\WebFramework\Form\Transformer;

use InvalidArgumentException;
use LM\WebFramework\DataStructures\Filename;
use LM\WebFramework\DataStructures\Slug;
use LM\WebFramework\ErrorHandling\Log;
use LM\WebFramework\Form\Exceptions\IllegalUserInputException;
use LM\WebFramework\Form\Exceptions\MissingInputException;
use LM\WebFramework\Model\Constraints\IUploadedImageConstraint;
use Psr\Http\Message\UploadedFileInterface;
use UnexpectedValueException;

final readonly class FileTransformer implements IFormTransformer
{
    public const PREVIOUS_SUFFIX = '_previous';

    const WEBP_QUALITY_HIGH = 95;
    const THUMBNAIL_FORMATS = [
        'small' => [
            'webp_quality' => 75,
            'min_width' => 316,
            'min_height' => 208,
        ],
        'medium' => [
            'webp_quality' => 85,
            'min_width' => 720,
            'min_height' => 502,
        ],
    ];

    public function __construct(
        private string $destinationFolder,
        private string $name,
        private bool $createThumbnails = true,
    ) {
    }

    /**
     * Save and convert the submitted file and return its path, or return the
     * previously submitted file.
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
     */
    private function extractPreviousFilename(array $parsedPayload): ?string
    {
        if (key_exists($this->name . self::PREVIOUS_SUFFIX, $parsedPayload)) {
            Log::info("Extracting previously uploaded file for {$this->name}.");
            $filename = new Filename($parsedPayload[$this->name . self::PREVIOUS_SUFFIX]);
            return $filename->__toString();
        } else {
            return null;
        }
    }

    private function createThumbnails(Filename $filename): void
    {
        $fileContent = file_get_contents("{$this->destinationFolder}/$filename");
        if (false === $fileContent) {
            throw new UnexpectedValueException("Failed to read the destination image '$filename' to create thumbnail.");
        }
        $originalImg = imagecreatefromstring($fileContent);
        if (false === $originalImg) {
            throw new UnexpectedValueException("Could not create GdImage from content of file '$filename'.");
        }
        list($width, $height) = [imagesx($originalImg), imagesy($originalImg)];

        foreach (self::THUMBNAIL_FORMATS as $key => $format) {
            $scale = max($format['min_width'] / $width, $format['min_height'] / $height);

            list($newWidth, $newHeight) = [(int) round($width * $scale), (int) round($height * $scale)];

            $thumbnailImg = imagecreatetruecolor($newWidth, $newHeight);

            imagecopyresized($thumbnailImg, $originalImg, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            imagewebp(
                $thumbnailImg,
                "{$this->destinationFolder}/{$filename->filenameNoExt}.$key.{$filename->extension}",
                $format['webp_quality'],
            );
        }
    }

    private function saveUploadedImage(UploadedFileInterface $uploadedFile): null|string
    {
        switch ($uploadedFile->getError()) {
            case UPLOAD_ERR_OK:
                $filename = new Filename($uploadedFile->getClientFilename());

                $sanitizedFilenameNoExt = new Slug($filename->filenameNoExt, transform: true)->__toString();

                $destinationFilename = "{$sanitizedFilenameNoExt}.webp";
                $destinationPath = "{$this->destinationFolder}/$destinationFilename";

                $i = 0;
                while (file_exists($destinationPath)) {
                    $destinationFilename = "{$sanitizedFilenameNoExt}-{$i}.webp";
                    $destinationPath = "{$this->destinationFolder}/$destinationFilename";
                    $i++;
                }

                $streamGdImg = imagecreatefromstring($uploadedFile->getStream()->getContents());
                imagewebp($streamGdImg, $destinationPath, self::WEBP_QUALITY_HIGH);


                if ($this->createThumbnails) {
                    $this->createThumbnails(new Filename($destinationFilename));
                }

                return $destinationFilename;

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
