<?php

declare(strict_types=1);

namespace LM\WebFramework\ErrorHandling;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

final class Log
{
    private static ?LoggerInterface $logger = null;

    /**
     * @param null|LoggerInterface $logger A PSR-3 compliant logger. If null, deactivate logging.
     */
    public static function init(?LoggerInterface $logger): void
    {
        self::$logger = $logger;
    }

    public static function log(string $msg, string $level): void
    {
        if (null !== self::$logger) {
            self::$logger->log($level, $msg);
        }
    }

    public static function debug(string $msg): void
    {
        self::log($msg, LogLevel::DEBUG);
    }

    public static function info(string $msg): void
    {
        self::log($msg, LogLevel::INFO);
    }

    // public static function notice(string $msg)
    // {
    //     self::log($msg, LogLevel::NOTICE);
    // }

    // public static function warn(string $msg)
    // {
    //     self::log($msg, LogLevel::WARNING);
    // }

    public static function error(string $msg): void
    {
        self::log($msg, LogLevel::ERROR);
    }

    // public static function critical(string $msg)
    // {
    //     self::log($msg, LogLevel::CRITICAL);
    // }
}
