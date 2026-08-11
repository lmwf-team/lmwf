<?php

declare(strict_types=1);

namespace LMWF\DataStructures;

use InvalidArgumentException;
use Stringable;
use UnexpectedValueException;

/**
 * Valid filename with a restrictive set of caracters allowed.
 */
final readonly class Filename implements Stringable
{
    const string ALLOWED_SEPS = '-';
    const string ALLOWED_LETTERS = 'abcdefghijklmnopqrstuvwxyz';

    public function __construct(
        public string $basename,
        public string $ext,
    ) {
        if (false === $this->isPartCorrect($ext, allowSep: false)) {
            throw new InvalidArgumentException("Extension is not valid.");
        }
        if (false === $this->isPartCorrect($basename, allowSep: true)) {
            throw new InvalidArgumentException("Filename is not valid.");
        }
    }

    public function __toString(): string
    {
        return $this->getFilename();
    }

    public static function fromString(string $filename, bool $transform = false): self
    {
        $parts = explode('.', $filename);
        $nParts = count($parts);
        if ($nParts !== 2) {
            throw new UnexpectedValueException('There should be exactly one dot in the filename (preceding the extension).');
        }
        if ($transform) {
            $parts = array_map(fn ($value) => Slug::transform($value), $parts);
        }

        return new self($parts[0], $parts[1]);
    }

    public function getFilename(): string
    {
        return "{$this->basename}.{$this->ext}";
    }

    public function isPartCorrect(string $part, bool $allowSep): bool
    {
        if ($allowSep) {
            if (1 === strspn($part, self::ALLOWED_SEPS, length: 1) || 1 === strspn($part, self::ALLOWED_SEPS, offset: -1)) {
                return false;
            }
        }
        return true === mb_check_encoding($part, 'ascii') && strlen($part) === strspn($part, $allowSep ? self::ALLOWED_LETTERS . self::ALLOWED_SEPS : self::ALLOWED_LETTERS);
    }

    public function withBasename(string $basename): self
    {
        return clone($this, [
            'basename' => $basename,
        ]);
    }

    public function withExt(string $ext): self
    {
        return clone($this, [
            'ext' => $ext,
        ]);
    }
}
