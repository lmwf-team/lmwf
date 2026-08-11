<?php

namespace MF\Model;

use LM\WebFramework\Constraints\StringConstraint;
use Stringable;
use UnexpectedValueException;
use voku\helper\ASCII;

class Slug implements Stringable
{
    private string $value;

    public function __construct(string $value, bool $transform = false, bool $allowEmpty = false) {
        if ($transform) {
            $this->value = substr(ASCII::to_slugify($value, language: 'fr'), 0, StringConstraint::MAX_LENGTH);
        } else {
            $this->value = $value;
        }
        if (!$allowEmpty && (0 === strlen($this->value) || 1 !== preg_match('/' . StringConstraint::REGEX_DASHES . '/', $this->value))) {
            throw new UnexpectedValueException($this->value);
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }
}