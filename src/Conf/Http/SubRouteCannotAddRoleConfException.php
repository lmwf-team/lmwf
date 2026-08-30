<?php

declare(strict_types=1);

namespace LMWF\Conf\Http;

use Exception;
use Throwable;

final class SubRouteCannotAddRoleConfException extends Exception
{
    const string MSG_FMT = "Unless explicitely authorized, a sub-route cannot allow roles its parent does not have, yet child node '%s' adds a role.";
    public function __construct(
        ?string $fqcn,
        int $code = 0,
        Throwable|null $previous = null,
    ) {
        parent::__construct(
            sprintf(self::MSG_FMT, $fqcn ?? '?'),
            $code,
            $previous,
        );
    }
}
