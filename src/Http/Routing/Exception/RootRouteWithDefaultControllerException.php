<?php

declare(strict_types=1);

namespace LMWF\Http\Routing\Exception;

use DomainException;
use LMWF\ErrorHandling\ExceptionCode;
use Throwable;

final class RootRouteWithDefaultControllerException extends DomainException
{
    public const string MESSAGE = 'The root route cannot define a controller, except, if it is a parameterized route which takes at least one parameter.';

    public function __construct(?Throwable $previous = null)
    {
        parent::__construct(
            self::MESSAGE,
            ExceptionCode::HTTP_ROUTING_ROOT_ROUTE_WITH_DFT_CONTROLLER->value,
            $previous,
        );
    }
}
