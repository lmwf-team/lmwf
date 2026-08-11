<?php

declare(strict_types=1);

namespace LM\WebFramework\ErrorHandling;

enum ExceptionCode: int
{
    case HTTP_ROUTING_ROOT_ROUTE_WITH_DFT_CONTROLLER = 070101;
}
