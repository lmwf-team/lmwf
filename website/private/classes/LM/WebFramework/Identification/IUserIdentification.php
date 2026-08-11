<?php

namespace LM\WebFramework\Identification;

use LM\Model\Username;

// TODO: (singleton?) class to get a IUserIdentification instance?
interface IUserIdentification
{
    public function isLoggedIn(): bool;
    public function getUsername(): Username;
    // TODO: use an interface instead? (IUsername)
    public function logUserIn(Username $username): void;
    public function logUserOut(): void;
}