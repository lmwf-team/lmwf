<?php

declare(strict_types=1);

namespace LM\WebFramework\Session;

final class SessionManager
{
    public const CSRF = 'csrf';

    public const CSRF_N_BYTES = 32;

    public const CURRENT_USERNAME_KEY = "cmu";

    public const CUSTOM_PREFIX = 'custom_';

    public const MESSAGES = 'messages';

    public const DEBUG_VARIABLES_SK = 'lmwf_debug_variables';

    private array $sessionData;

    public function __construct(?array $sessionData = null)
    {
        if (null === $sessionData) {
            session_start();
            $this->sessionData =& $_SESSION;
        } else {
            $this->sessionData = $sessionData;
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

    /**
     * @todo Throw exception if not in dev mode.
     */
    public function addDebugVariable($variable): void
    {
        if (key_exists(self::DEBUG_VARIABLES_SK, $this->sessionData)) {
            $this->sessionData[self::DEBUG_VARIABLES_SK][] = $variable;
        } else {
            $this->sessionData[self::DEBUG_VARIABLES_SK] = [
                $variable,
            ];
        }
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
     * @todo Throw exception if not in dev mode.
     */
    public function getAndDeleteDebugVariables(): array
    {
        $variables = $this->sessionData[self::DEBUG_VARIABLES_SK] ?? [];
        $this->sessionData[self::DEBUG_VARIABLES_SK] = [];
        return $variables;
    }

    public function getAndDeleteMessages(): array
    {
        $messages = $this->sessionData[self::MESSAGES] ?? [];
        $this->sessionData[self::MESSAGES] = [];
        return $messages;
    }
}
