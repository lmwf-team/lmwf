<?php

declare(strict_types=1);

namespace LM\WebFramework\ErrorHandling;

final class Logger
{
    public static function log(string $msg, LogLevel $level)
    {
        switch ($level) {
            case LogLevel::NOTICE:
            default:
                trigger_error($msg);
        }
    }

    public static function notice(string $msg)
    {
        self::log($msg, LogLevel::NOTICE);
    }
}
