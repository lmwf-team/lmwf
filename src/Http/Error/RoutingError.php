<?php

declare(strict_types=1);

namespace LMWF\Http\Error;

enum RoutingError
{
    case RouteNotFound;
    case UnsupportedArgs;
}
