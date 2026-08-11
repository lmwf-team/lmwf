<?php

declare(strict_types=1);

namespace LM\WebFramework\DataStructures;

use LM\WebFramework\Model\Constraints\StringConstraint;
use Stringable;
use UnexpectedValueException;
use voku\helper\ASCII;

final class Slug implements Stringable
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