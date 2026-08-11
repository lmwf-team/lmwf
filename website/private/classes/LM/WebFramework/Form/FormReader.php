<?php

namespace LM\WebFramework\Form;

use \LM\Model\Username;
use \LM\Model\Password;

class FormReader
{
	private $errors;
	private $passwords;
	private $postData;
	private $usernames;

	public function __construct(array $postData)
	{
		$this->errors = array();
		$this->passwords = array();
		$this->postData = $postData;
		$this->usernames = array();
	}

	public function readUsername(string $fieldName): void
	{
		try {
			$username = new Username($this->postData[$fieldName]);
			$this->usernames[$fieldName] = $username;
		} catch (\InvalidArgumentException $e) {
			$this->errors[$fieldName] = 'Nom d\'utilisateur invalide.';
		}
	}

	public function readPassword(string $fieldName): void
	{
		try {
			$password = new Password($this->postData[$fieldName]);
			$this->passwords[$fieldName] = $password;
		} catch (\InvalidArgumentException $e) {
			$this->errors[$fieldName] = 'Mot de passe invalide.';
		}
	}

	public function getError(string $fieldName): string
	{
		return $this->errors[$fieldName];
	}

	public function getPassword(string $fieldName): Password
	{
		return $passwords[$fieldName];
	}

	public function getUsername(string $fieldName): Username
	{
		return $this->usernames[$fieldName];
	}

	public function hasErrorFor(string $fieldName): bool
	{
		return isset($this->errors[$fieldName]);
	}

	public function hasErrors(): bool
	{
		return 0 !== sizeof($this->errors);
	}
}