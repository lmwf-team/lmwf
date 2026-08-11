<?php

declare(strict_types=1);

namespace LM\WebFramework\DataStructures;

use LM\WebFramework\Constraint\Factory\self;
use Stringable;
use UnexpectedValueException;
use voku\helper\ASCII;

/**
 * @todo Add tests with some sort of fuzzing.
 * @todo Do not use dependency?
 * @todo web namespace?
 */
final class Slug implements Stringable
{
    public const int SLUG_MAX_LENGTH = 255;

    public const int SLUG_MIN_LENGTH = 1;

    public const string SLUG_REGEX = '^(([a-z0-9])-?)*(?2)+$';

    private string $value;

    public function __construct(string $value, bool $transform = false, bool $allowEmpty = false)
    {
        if ($transform) {
            $this->value = substr(ASCII::to_slugify($value, language: 'fr'), 0, self::SLUG_MAX_LENGTH);
        } else {
            $this->value = $value;
        }
        if (!$allowEmpty && (0 === strlen($this->value) || 1 !== preg_match('/' . self::SLUG_REGEX . '/', $this->value))) {
            throw new UnexpectedValueException($this->value);
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
