<?php

declare(strict_types=1);

namespace LM\WebFramework\Http\Routing\Exception;

use DomainException;
use LM\WebFramework\ExceptionCode;
use Throwable;

final class RootRouteWithDefaultControllerException extends DomainException
{
    const string MESSAGE = 'The root route cannot define a controller, except, if it is a parameterized route, when it receives at least one parameter.';

    public function __construct(?Throwable $previous = null)
    {
        parent::__construct(
            self::MESSAGE,
            ExceptionCode::HTTP_ROUTING_ROOT_ROUTE_WITH_DFT_CONTROLLER->value,
            $previous,
        );
    }
}
