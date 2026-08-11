<?php

declare(strict_types=1);

namespace LM\WebFramework\DataStructures;

use LM\WebFramework\Model\Factory\SlugModelFactory;
use Stringable;
use UnexpectedValueException;
use voku\helper\ASCII;

/**
 * @todo web namespace?
 */
final class Slug implements Stringable
{
    private string $value;

    public function __construct(string $value, bool $transform = false, bool $allowEmpty = false) {
        if ($transform) {
            $this->value = substr(ASCII::to_slugify($value, language: 'fr'), 0, SlugModelFactory::SLUG_MAX_LENGTH);
        } else {
            $this->value = $value;
        }
        if (!$allowEmpty && (0 === strlen($this->value) || 1 !== preg_match('/' . SlugModelFactory::SLUG_REGEX . '/', $this->value))) {
            throw new UnexpectedValueException($this->value);
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }
}