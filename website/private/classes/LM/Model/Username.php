<?php

namespace LM\Model;

class Username
{
	const MAX_LENGTH = 255;
	const MIN_LENGTH = 1;

	private $usernameString;

	public function __construct($usernameString)
	{
		if (is_string($usernameString) &&
            strlen($usernameString) >= self::MIN_LENGTH &&
            strlen($usernameString) <= self::MAX_LENGTH) {
			$this->usernameString = $usernameString;
		} else {
            throw new \InvalidArgumentException;
        }
	}

    public function getString(): string
    {
        return $this->usernameString;
    }
}