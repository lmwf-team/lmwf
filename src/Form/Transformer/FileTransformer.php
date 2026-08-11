<?php

declare(strict_types=1);

namespace LM\WebFramework\Form\Transformer;

use LM\WebFramework\DataStructures\Filename;
use LM\WebFramework\DataStructures\Slug;
use LM\WebFramework\ErrorHandling\Log;
use LM\WebFramework\Form\Exceptions\IllegalUserInputException;
use LM\WebFramework\Form\Exceptions\MissingInputException;
use LM\WebFramework\Model\Constraints\IUploadedImageConstraint;
use Psr\Http\Message\UploadedFileInterface;

final readonly class FileTransformer implements IFormTransformer
{
    public const PREVIOUS_SUFFIX = '_previous';

    public function __construct(
        private string $destinationFolder,
        private string $name,
        private bool $createThumbnails = true,
    ) {
    }

    /**
     * @throws MissingInputException If no file was uploaded.
     */
    public function transformSubmittedData(array $postedData, array $uploadedFiles): null|array|string
    {
        if (!key_exists($this->name, $uploadedFiles)) {
            return $this->extractPreviousFilename($postedData);
        }

        $uploaded = $uploadedFiles[$this->name];

        if (is_array($uploaded)) {
            $filenames = [];
            foreach ($uploaded as $img) {
                $filenames[] = $this->saveUploadedImage($img) ?? $this->extractPreviousFilename($postedData);
            }
            return $filenames;
        } else {
            return $this->saveUploadedImage($uploaded) ?? $this->extractPreviousFilename($postedData);
        }
    }

    private function extractPreviousFilename(array $postedData): null|string
    {
        if (key_exists($this->name . self::PREVIOUS_SUFFIX, $postedData)) {
            Log::info("Extracting previously uploaded file for {$this->name}.");
            $oldFilename = pathinfo($postedData[$this->name . self::PREVIOUS_SUFFIX]);
            if ('.' !== $oldFilename['dirname']) {
                throw new IllegalUserInputException();
            }
            return $oldFilename['basename'];
        } else {
            return null;
        }
    }

    private function saveUploadedImage(UploadedFileInterface $file): null|string
    {

        if (0 === $file->getError()) {
            $extension = 'webp';
            $uploadedFileName = pathinfo($file->getClientFilename(), PATHINFO_FILENAME);
            $newFilename = (new Slug($uploadedFileName, true, true))->__toString();
            $destinationPath = "{$this->destinationFolder}/{$newFilename}.$extension";

            $i = 0;
            while (file_exists($destinationPath)) {
                $destinationPath = "{$this->destinationFolder}/{$newFilename}-{$i}.$extension";
                $i++;
            }

            if ('png' !== $extension) {
                $streamGdImg = imagecreatefromstring($file->getStream()->getContents());
                imagewebp($streamGdImg, $destinationPath, 95);
            } else {
                $file->moveTo($destinationPath);
            }


            if ($this->createThumbnails) {
                $this->createThumbnail(new Filename($destinationPath), 'small', 316, 208, 75);
                $this->createThumbnail(new Filename($destinationPath), 'medium', 720, 502, 85);
            }

            return pathinfo($destinationPath)['basename'];
        } elseif (1 == $file->getError()) {
            return IUploadedImageConstraint::FILE_TOO_BIG_ERROR;
        } elseif (4 === $file->getError()) {
            return null;
        } else {
            return IUploadedImageConstraint::MISC_ERROR;
        }
    }

    private function createThumbnail(Filename $originalPath, string $suffix, int $minWidth, int $minHeight, int $quality)
    {
        $originalImg = imagecreatefromstring(file_get_contents($originalPath->__toString()));

        list($width, $height) = [imagesx($originalImg), imagesy($originalImg)];

        $scale = max($minWidth / $width, $minHeight / $height);

        list($newWidth, $newHeight) = [(int) round($width * $scale), (int) round($height * $scale)];

        $thumbnail = imagecreatetruecolor($newWidth, $newHeight);

        imagecopyresized($thumbnail, $originalImg, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        imagewebp(
            $thumbnail,
            $originalPath->getFilenameNoExtension() . '.' . $suffix . '.' . $originalPath->getExtension(),
            $quality,
        );
    }
}
