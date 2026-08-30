<?php

declare(strict_types=1);

namespace LMWF\Tests\Factory;

use LMWF\Conf\Http\RouteDef;
use LMWF\Http\DataStructures\StaticPageConf;
use LMWF\Http\Routing\Route;

final readonly class RouteFactory
{
    /**
     * @param non-empty-array<string, RouteDef> $subRouteDefs
     */
    public static function createRootRoute(
        array $subRouteDefs = [],
    ): Route {
        $routeDef = new RouteDef(subRouteDefs: $subRouteDefs);
        return new Route($routeDef, '', parent: null);
    }


    public static function createDef(
        string $title = '_',
        string $controllerFqcn = Controller2::class,
        string $baseUrl = '_',
        bool $isIndexed = true,
        bool $isPartOfHierarchy = true,
        array $subRouteDefs = [],
        int $nParamsLeast = 0,
        int $nParamsMax = 0,
    ): RouteDef {
        return new RouteDef([
            0 => new StaticPageConf($title, $controllerFqcn, $baseUrl, $isIndexed, $isPartOfHierarchy),
        ], subRouteDefs: $subRouteDefs, nParamsLeast: $nParamsLeast, nParamsMax: $nParamsMax);
    }
}
