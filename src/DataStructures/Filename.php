<?php

declare(strict_types=1);

namespace LM\WebFramework\DataStructures;

use InvalidArgumentException;
use Stringable;
use UnexpectedValueException;

final readonly class Filename implements Stringable
{
    public string $extension;
    public string $filename;
    public string $filenameNoExt;

    public function __construct(string $filename)
    {
        // Check the given string is UTF-8
        if (false === mb_check_encoding($filename, 'UTF-8')) {
            throw new InvalidArgumentException("Filename '{$filename}' must be UTF-8 encoded.");
        }

        // Normalize directory separators
        $filename = str_replace('\\', '/', $filename);

        // Makes sure the filename contains no directory sepaerators
        if (str_contains($filename, '/')) {
            throw new UnexpectedValueException('Filename must not contain directory separators.');
        }

        // Check the path is not only dots or is not empty
        if ('' === str_replace('.', '', $filename)) {
            throw new InvalidArgumentException('Filename must not be an empty string or only contain dots.');
        }

        $parts = explode('.', $filename);
        $nParts = count($parts);
        if ($nParts < 2) {
            throw new UnexpectedValueException('There should be at least one dot in the filename (preceding the extension).');
        }

        $this->filename = $filename;
        $this->extension = $parts[$nParts - 1];
        $this->filenameNoExt = substr($filename, 0, strlen($filename) - strlen($this->extension) - 1);
    }

    public function __toString(): string
    {
        return $this->filename;
    }
}
