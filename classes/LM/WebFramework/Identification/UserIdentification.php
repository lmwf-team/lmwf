<?php

namespace LM\WebFramework\Identification;

use \LM\Model\Username;

class UserIdentification implements IUserIdentification
{
    public function isLoggedIn(): bool
    {
        try {
            // TODO: find more meaningful way to test if username session variable is set and is valid.
            $this->getUsername();
            return true;
        } catch (\exception $e) { // TODO: more precise exception
            return false;
        }
    }

    public function getUsername(): Username
    {
        $username = new Username($_SESSION['current_user_username']);
        return $username;
    }

    public function logUserIn(Username $username): void
    {
        $_SESSION['current_user_username'] = $username->getString();
    }

    public function logUserOut(): void
    {
        unset($_SESSION['current_user_username']);
    }
}