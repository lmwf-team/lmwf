<?php

declare(strict_types=1);

namespace LM\WebFramework\Http\Routing\Exception;

use Exception;
use Throwable;

final class UnauthorizedAttributeConfException extends Exception
{
    const string MSG_FMT = "Attribute '%s' is unknown and not allowed in a route definition.";

    public function __construct(string $key, int $code = 0, Throwable|null $previous = null)
    {
        parent::__construct(sprintf(self::MSG_FMT, $key), $code, $previous);
    }
}
