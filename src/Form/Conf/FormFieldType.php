<?php

declare(strict_types=1);

namespace LM\WebFramework\Form\Conf;

use OutOfBoundsException;

enum FormFieldType: string
{
    case Checkbox = 'checkbox';
    case Date = 'date';
    case Img = 'img';
    case Int = 'int';
    case Text = 'text';
    case Textarea = 'textarea';

    public static function fromString(string $value): self
    {
        foreach (self::cases() as $case) {
            if ($value === $case->value) {
                return $case;
            }
        }
        throw new OutOfBoundsException();
    }
}