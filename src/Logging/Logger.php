<?php

declare(strict_types=1);

namespace LM\WebFramework\Logging;

use LM\WebFramework\Configuration;

final class Logger
{
    private string $prefix;

    public function __construct(
        Configuration $configuration,
    ) {
        $this->prefix = $configuration->getLoggingPrefix();
    }

    public function log(string $message): void
    {
        echo "{$this->prefix}: {$message}\n";
    }
}