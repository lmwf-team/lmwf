<?php

declare(strict_types=1);

namespace LMWF\Profiling;

final class Profiler
{
    public static float $dbTime = 0;
    public static float $viewTime = 0;
    public static float $logTime = 0;
    public static float $totalTime = 0;
}
