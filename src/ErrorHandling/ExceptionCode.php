<?php

declare(strict_types=1);

namespace LMWF\ErrorHandling;

enum ExceptionCode: int
{
    case HTTP_ROUTING_ROOT_ROUTE_WITH_DFT_CONTROLLER = 70101;
    case APP_TRAVERSABLE_UNEXPECTED_PROPERTY_TYPE = 80101;
}
