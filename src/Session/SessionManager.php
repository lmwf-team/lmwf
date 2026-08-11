<?php

declare(strict_types=1);

namespace LM\WebFramework\Session;

final class SessionManager
{
    public const CSRF = 'csrf';

    public const CSRF_N_BYTES = 32;

    public const CURRENT_MEMBER_USERNAME = "cmu";

    public const CUSTOM_PREFIX = 'custom_';

    public const MESSAGES = 'messages';

    public function getCsrf(): string
    {
        return $_SESSION[self::CSRF] ?? $_SESSION[self::CSRF] = bin2hex(random_bytes(self::CSRF_N_BYTES));
    }

    public function getCurrentMemberUsername(): ?string
    {
        if ($this->isUserLoggedIn()) {
            return $_SESSION[self::CURRENT_MEMBER_USERNAME];
        } else {
            return null;
        }
    }

    public function getCustom(string $key): string
    {
        return $_SESSION[self::CUSTOM_PREFIX . $key];
    }

    public function isUserLoggedIn(): bool
    {
        return key_exists(self::CURRENT_MEMBER_USERNAME, $_SESSION) && null !== $_SESSION[self::CURRENT_MEMBER_USERNAME];
    }

    /**
     * @todo Should not accept null.
     */
    public function setCurrentMemberUsername(?string $username): void
    {
        $_SESSION[self::CURRENT_MEMBER_USERNAME] = $username;
    }

    public function setCustom(string $key, string $value): void
    {
        $_SESSION[self::CUSTOM_PREFIX . $key] = $value;
    }

    public function addMessage(string $message): void
    {
        if (key_exists(self::MESSAGES, $_SESSION)) {
            $_SESSION[self::MESSAGES][] = $message;
        } else {
            $_SESSION[self::MESSAGES] = [
                $message,
            ];
        }
    }

    public function getAndDeleteMessages(): array
    {
        $messages = $_SESSION[self::MESSAGES] ?? [];
        $_SESSION[self::MESSAGES] = [];
        return $messages;
    }
}
