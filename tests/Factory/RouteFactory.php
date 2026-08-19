<?php

declare(strict_types=1);

namespace LMWF\Tests\Factory;

use DomainException;
use LMWF\Conf\Http\RouteDef;
use LMWF\Http\DataStructures\PageConf;
use LMWF\Http\Routing\Route;

final readonly class RouteFactory
{
    /**
     * @param non-empty-array<string, RouteDef> $subroutes
     */
    public static function createRootRoute(
        array $subroutes,
    ): Route {
        $routeDef = new RouteDef(null, null, subroutes: $subroutes);
        return new Route($routeDef, '', parent: null);
    }
}
