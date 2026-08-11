<?php

declare(strict_types=1);

namespace LM\WebFramework\Configuration\Exception;

use RuntimeException;
use Throwable;

final class CouldNotReadFileException extends RuntimeException
{
    const string MSG_FMT = "The file %s could not be read.";

    public function __construct(string $filePath, int $code = 0, Throwable|null $previous = null)
    {
        parent::__construct(sprintf(self::MSG_FMT, $filePath), $code, $previous);
    }
}
