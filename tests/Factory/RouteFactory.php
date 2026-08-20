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
     * @param non-empty-array<string, RouteDef> $subRouteDefs
     */
    public static function createRootRoute(
        array $subRouteDefs,
    ): Route {
        $routeDef = new RouteDef(fqcn: null, pageParam: null, subRouteDefs: $subRouteDefs);
        return new Route($routeDef, '', parent: null);
    }
}
