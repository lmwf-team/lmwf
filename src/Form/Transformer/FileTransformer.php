<?php

namespace LM\WebFramework\Form\Transformer;

use GdImage;
use LM\WebFramework\DataStructures\Filename;
use LM\WebFramework\Form\Exceptions\IllegalUserInputException;
use LM\WebFramework\Form\Exceptions\MissingInputException;
use MF\Model\Slug;
use Psr\Http\Message\UploadedFileInterface;
use UnexpectedValueException;

class FileTransformer implements IFormTransformer
{
    const PREVIOUS_SUFFIX = '_previous';

    private bool $createThumbnails;

    private string $name;

    private string $destinationFolder;

    public function __construct(
        string $destinationFolder,
        string $name,
        bool $createThumbnails = true,
    ) {
        $this->name = $name;
        $this->destinationFolder = $destinationFolder;
        $this->createThumbnails = $createThumbnails;
    }

    /**
     * @throws MissingInputException If no file was uploaded.
     * @todo Do not save the image here, just extract the a UploadedFileInterface or an array of it.
     */
    public function extractValueFromRequest(array $formRawData, array $uploadedFiles): null|array|string {
        if (!key_exists($this->name, $uploadedFiles)) {
            return $this->extractPreviousFilename($formRawData);
        }

        $uploaded = $uploadedFiles[$this->name];

        if (is_array($uploaded)) {
            $filenames = [];
            foreach ($uploaded as $img) {
                $filenames[] = $this->saveUploadedImage($img) ?? $this->extractPreviousFilename($formRawData);
            }
            return $filenames;
        } else {
            return $this->saveUploadedImage($uploaded) ?? $this->extractPreviousFilename($formRawData);
        }
    }

    private function extractPreviousFilename(array $formRawData): null|string {
        if (key_exists($this->name . self::PREVIOUS_SUFFIX, $formRawData)) {
            $oldFilename = pathinfo($formRawData[$this->name . self::PREVIOUS_SUFFIX]);
            if ('.' !== $oldFilename['dirname']) {
                throw new IllegalUserInputException();
            }
            return $oldFilename['basename'];
        } else {
            return null;
        }
    }

    private function saveUploadedImage(UploadedFileInterface $file): null|string {
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

            if('png' !== $extension) {
                $streamGdImg = imagecreatefromstring($file->getStream());
                imagewebp($streamGdImg, $destinationPath, 95);
            } else {
                $file->moveTo($destinationPath);
            }


            if ($this->createThumbnails) {
                $this->createThumbnail(new Filename($destinationPath), 'small', 316, 208, 75);
                $this->createThumbnail(new Filename($destinationPath), 'medium', 720, 502, 85);
            }

            return pathinfo($destinationPath)['basename'];
        } elseif (4 === $file->getError()) {
            return null;
        } else {
            throw new UnexpectedValueException();
        }
    }

    private function createThumbnail(Filename $originalPath, string $suffix, int $minWidth, int $minHeight, int $quality) {
        $originalImg = imagecreatefromstring(file_get_contents($originalPath));

        list($width, $height) = [imagesx($originalImg), imagesy($originalImg)];

        $scale = max($minWidth / $width, $minHeight / $height);

        list($newWidth, $newHeight) = [round($width * $scale), round($height * $scale)];

        $thumbnail = imagecreatetruecolor($newWidth, $newHeight);

        imagecopyresized($thumbnail, $originalImg, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        imagewebp(
            $thumbnail,
            $originalPath->getFilenameNoExtension() . '.' . $suffix . '.' . $originalPath->getExtension(),
            $quality,
        );
    }
}