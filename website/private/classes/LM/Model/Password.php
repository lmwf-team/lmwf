<?php

namespace LM\Model;

class Password
{
    const MAX_LENGTH = 255;
    const MIN_LENGTH = 1;
    private $passwordString;

    public function __construct($passwordString)
    {
        if (is_string($passwordString) &&
            strlen($passwordString) >= self::MIN_LENGTH &&
            strlen($passwordString) <= self::MAX_LENGTH) {
            $this->passwordString = $passwordString;
        } else {
            throw new \InvalidArgumentException;
        }
    }

    public function getString(): string
    {
        return $this->passwordString;
    }
}