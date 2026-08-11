<?php

declare(strict_types=1);

namespace LMWF\DataStructures;

use InvalidArgumentException;
use Stringable;
use UnexpectedValueException;

final class KeyName implements Stringable
{
    public const INPUT_SEPARATORS = [
        ' ',
        ' ',
        '-',
        ',',
    ];

    public const CAMEL_BACK_ATTRIBUTE_REGEX = '/^[a-z]+([A-Z][a-z]*)*$/';

    private string $value;

    public function __construct(string $stringInput)
    {
        if (1 === preg_match(self::CAMEL_BACK_ATTRIBUTE_REGEX, $stringInput)) {
            $underscored = preg_replace('/[A-Z]/', '_$0', $stringInput);
            if (null === $underscored) {
                throw new UnexpectedValueException('Got null when trying to convert camel back to snake case.');
            }
            $this->value = $this->convert($underscored);
        } else {
            $this->value = $this->convert($stringInput);
        }
        if (0 === strlen($this->value)) {
            throw new InvalidArgumentException("$stringInput was transformed to an empty string.");
        }
    }

    public function convert(string $stringInput): string
    {
        $stringUnderscore = str_replace(self::INPUT_SEPARATORS, '_', $stringInput);
        $stringLowercase = strtolower($stringUnderscore);
        $stringAscii = preg_replace('/[^a-z0-9_]/', '', $stringLowercase);
        if (null === $stringAscii) {
            throw new UnexpectedValueException('Got null unexpectedly when trying to convert string to ASCII.');
        }
        $stringConverted = preg_replace('/(_{2,})|(^_+)|(_+$)/', '', $stringAscii);
        if (null === $stringConverted) {
            throw new UnexpectedValueException('Converted string is unexpectedly null.');
        }

        return $stringConverted;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
