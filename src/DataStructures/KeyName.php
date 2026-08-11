<?php

declare(strict_types=1);

namespace LM\WebFramework\DataStructures;

use InvalidArgumentException;
use Stringable;

final class KeyName implements Stringable
{
    const INPUT_SEPARATORS = [
        ' ',
        ' ',
        '-',
        ',',
    ];

    const CAMEL_BACK_ATTRIBUTE_REGEX = '/^[a-z]+([A-Z][a-z]*)*$/';

    private string $value;

    public function __construct(string $stringInput)
    {
        if (preg_match(self::CAMEL_BACK_ATTRIBUTE_REGEX, $stringInput)) {
            $this->value = $this->convert(preg_replace('/[A-Z]/', '_$0', $stringInput));
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
        $stringConverted = preg_replace('/(_{2,})|(^_+)|(_+$)/', '', $stringAscii);

        return $stringConverted;
    }

    public function __toString(): string {
        return $this->value;
    }
}