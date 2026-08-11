<?php

declare(strict_types=1);

namespace LM\WebFramework\Http\Error;

enum RoutingError {
    case RouteNotFound;
    case UnsupportedArgs;
};