<?php

declare(strict_types=1);

namespace LM\WebFramework\Session;

use LM\WebFramework\Kernel;

final class SessionManager
{
    public const CSRF = 'csrf';

    public const CSRF_N_BYTES = 32;

    public const CURRENT_USERNAME_KEY = "cmu";

    public const CUSTOM_PREFIX = 'custom_';

    public const MESSAGES = 'messages';

    /**
     * @var mixed[]
     */
    private array $sessionData;

    /**
     * @param null|mixed[] $sessionData
     */
    public function __construct(?array $sessionData = null)
    {
        if (null !== $sessionData || Kernel::CLI_ID === php_sapi_name()) {
            $this->sessionData = $sessionData ?? [];
        } else {
            session_start();
            $this->sessionData =& $_SESSION;
        }
    }

    public function getCsrf(): string
    {
        return $this->sessionData[self::CSRF] ?? $this->sessionData[self::CSRF] = bin2hex(random_bytes(self::CSRF_N_BYTES));
    }

    public function getCurrentUsername(): ?string
    {
        if ($this->isUserLoggedIn()) {
            return $this->sessionData[self::CURRENT_USERNAME_KEY];
        } else {
            return null;
        }
    }

    public function getCustom(string $key): string
    {
        return $this->sessionData[self::CUSTOM_PREFIX . $key];
    }

    public function isUserLoggedIn(): bool
    {
        return key_exists(self::CURRENT_USERNAME_KEY, $this->sessionData) && null !== $this->sessionData[self::CURRENT_USERNAME_KEY];
    }

    /**
     * @todo Should not accept null.
     */
    public function setCurrentUsername(?string $username): void
    {
        $this->sessionData[self::CURRENT_USERNAME_KEY] = $username;
    }

    public function setCustom(string $key, string $value): void
    {
        $this->sessionData[self::CUSTOM_PREFIX . $key] = $value;
    }

    public function addMessage(string $message): void
    {
        if (key_exists(self::MESSAGES, $this->sessionData)) {
            $this->sessionData[self::MESSAGES][] = $message;
        } else {
            $this->sessionData[self::MESSAGES] = [
                $message,
            ];
        }
    }

    /**
     * @return string[]
     */
    public function getAndDeleteMessages(): array
    {
        $messages = $this->sessionData[self::MESSAGES] ?? [];
        $this->sessionData[self::MESSAGES] = [];
        return $messages;
    }
}
