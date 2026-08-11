<?php

declare(strict_types=1);

namespace LMWF\Session;

use LMWF\DataStructures\Exceptions\UnexpectedPropertyType;
use UnexpectedValueException;

/**
 * @todo Should be moved to Http? Would make it easier to Http to access the
 * session, but Session does not need to access Http. Besides, Form only
 * relies on Session but does not rely on Http.
 */
final class SessionManager
{
    public const CSRF = 'csrf';

    public const CSRF_N_BYTES = 32;

    public const CURRENT_USERNAME_KEY = "cmu";

    public const CUSTOM_PREFIX = 'custom_';

    public const MESSAGES = 'messages';

    /**
     * @var array<mixed>
     */
    private array $data;

    /**
     * @todo Magic string.
     * @param null|array<string, scalar> $data
     */
    public function __construct(?array $data = null)
    {
        if (null !== $data || 'cli' === php_sapi_name()) {
            $this->data = $data ?? [];
        } else {
            session_start();
            $this->data =& $_SESSION;
        }
    }

    public function getCsrf(): string
    {
        if (key_exists(self::CSRF, $this->data)) {
            $csrf = $this->data[self::CSRF];
            if (!is_string($csrf)) {
                throw new UnexpectedValueException("Expected session-stored CSRF to be string.");
            }
            return $csrf;
        }
        $this->data[self::CSRF] = $csrf = bin2hex(random_bytes(self::CSRF_N_BYTES));
        return $csrf;
    }

    public function getCurrentUsername(): ?string
    {
        if ($this->isUserLoggedIn()) {
            $currentUsername = $this->data[self::CURRENT_USERNAME_KEY];
            if (!is_null($currentUsername) && !is_string($currentUsername)) {
                throw new UnexpectedValueException("Expected session-stored username to either be a string or a null.");
            }
            return $currentUsername;
        } else {
            return null;
        }
    }

    public function getCustom(string $key): string
    {
        $value = $this->data[self::CUSTOM_PREFIX . $key];
        if (!is_string($value)) {
            throw new UnexpectedValueException("Expected session value with key '$key' to be a string.");
        }
        return $value;
    }

    public function isUserLoggedIn(): bool
    {
        return key_exists(self::CURRENT_USERNAME_KEY, $this->data) && null !== $this->data[self::CURRENT_USERNAME_KEY];
    }

    /**
     * @todo Should not accept null.
     */
    public function setCurrentUsername(?string $username): void
    {
        $this->data[self::CURRENT_USERNAME_KEY] = $username;
    }

    public function setCustom(string $key, mixed $value): void
    {
        $this->data[self::CUSTOM_PREFIX . $key] = $value;
    }

    public function addMessage(string $msg): void
    {
        if (key_exists(self::MESSAGES, $this->data)) {
            if (!is_array($this->data[self::MESSAGES]) || !array_is_list($this->data[self::MESSAGES])) {
                throw new UnexpectedPropertyType(self::MESSAGES, 'array', $this->data[self::MESSAGES]);
            }
            $this->data[self::MESSAGES][] = $msg;
        } else {
            $this->data[self::MESSAGES] = [
                $msg,
            ];
        }
    }

    /**
     * @return list<string>
     */
    public function getAndDeleteMessages(): array
    {
        if (key_exists(self::MESSAGES, $this->data)) {
            $msgs = $this->data[self::MESSAGES];
            if (!is_array($msgs) || !array_is_list($msgs)) {
                throw new UnexpectedValueException("Expected list of messages in session to be a list.");
            }
            foreach ($msgs as $msg) {
                if (!is_string($msg)) {
                    throw new UnexpectedValueException("Expected list of messages in session to only contain strings.");
                }
            }
            $this->data[self::MESSAGES] = [];
            return $msgs;
        }

        $this->data[self::MESSAGES] = [];
        return [];
    }
}
