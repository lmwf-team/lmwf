<?php

declare(strict_types=1);

namespace LM\WebFramework\Http\Routing\Exception;

use Exception;
use Throwable;

final class SubRouteCannotAddRoleConfException extends Exception
{
    const string MSG_FMT = "Unless explicitely authorized, a sub-route cannot add roles its parent does not have. Child node '%s' requires role '%s'.";

    public function __construct(
        array $route,
        string $role,
        int $code = 0,
        Throwable|null $previous = null,
    ) {
        parent::__construct(
            sprintf(self::MSG_FMT, $route['fqcn'] ?? '?', $role),
            $code,
            $previous,
        );
    }
}
