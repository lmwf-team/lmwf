<?php

declare(strict_types=1);

namespace LM\WebFramework\DataStructures;

use Stringable;
use UnexpectedValueException;

final class Filename implements Stringable
{
    private string $extension;

    private string $filenameNoExtension;

    public function __construct(string $filename) {
        $parts = explode('.', $filename);
        $nParts = count($parts);
        if ($nParts < 2) {
            throw new UnexpectedValueException('There should be at least one dot in the filename (preceding the extension).');
        }
        $this->extension = $parts[$nParts - 1];
        $this->filenameNoExtension = substr($filename, 0, strlen($filename) - strlen($this->extension) - 1);
    }

    public function getExtension(): string {
        return $this->extension;
    }

    public function getFilenameNoExtension(): string {
        return $this->filenameNoExtension;
    }

    public function __toString(): string {
        return $this->filenameNoExtension . '.' . $this->extension;
    }
}