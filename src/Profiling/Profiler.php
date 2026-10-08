<?php

declare(strict_types=1);

namespace LMWF\Profiling;

final class Profiler
{
    public static float $dbTime = 0;
    public static float $logTime = 0;
    public static float $totalTime = 0;
    public static float $viewTime = 0;

    public static function start(): float
    {
        return microtime(as_float: true);
    }

    public static function end(float $start): float
    {
        return microtime(as_float: true) - $start;
    }

    public static function toCsvRow(): string
    {
        return sprintf('%.8F,%.8F,%.8F,%.8F', self::$dbTime, self::$logTime, self::$totalTime, self::$viewTime);
    }
}
