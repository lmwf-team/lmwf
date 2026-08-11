<?php

declare(strict_types=1);

namespace LM\WebFramework\Session;

class SessionManager
{
    const CSRF = 'csrf';

    const CSRF_N_BYTES = 32;

    const CURRENT_MEMBER_USERNAME = "cmu";

    const MESSAGES = 'messages';

    public function getCurrentMemberUsername(): ?string {
        if (isset($_SESSION[self::CURRENT_MEMBER_USERNAME]) && null !== $_SESSION[self::CURRENT_MEMBER_USERNAME]) {
            return $_SESSION[self::CURRENT_MEMBER_USERNAME];
        } else {
            return null;
        }
    }

    public function isUserLoggedIn(): bool {
        return isset($_SESSION[self::CURRENT_MEMBER_USERNAME]) && null !== $_SESSION[self::CURRENT_MEMBER_USERNAME];
    }

    public function setCurrentMemberUsername(?string $username): void {
        $_SESSION[self::CURRENT_MEMBER_USERNAME] = $username;
    }

    public function getCsrf(): string {
        return $_SESSION[self::CSRF] ?? $_SESSION[self::CSRF] = bin2hex(random_bytes(self::CSRF_N_BYTES));
    }

    public function addMessage(string $message): void {
        isset($_SESSION[self::MESSAGES]) ? $_SESSION[self::MESSAGES][] = $message : $_SESSION[self::MESSAGES] = [$message];
    }

    public function getAndDeleteMessages(): array {
        $messages = $_SESSION[self::MESSAGES] ?? [];
        $_SESSION[self::MESSAGES] = [];
        return $messages;
    }
}