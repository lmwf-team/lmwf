<?php

declare(strict_types=1);

namespace LMWF\Tests\Factory;

use LMWF\Http\DataStructures\RouteDef;
use LMWF\Http\DataStructures\StaticPageConf;
use LMWF\Http\Routing\Route;
use LMWF\Tests\Mocks\UnderscoreController;

final readonly class RouteFactory
{
    /**
     * @param array<string, RouteDef> $children
     */
    public static function createRootRoute(
        array $children = [],
    ): Route {
        $routeDef = new RouteDef(children: $children);
        return new Route($routeDef, parent: null, seg: '');
    }

    /**
     * @param  array<string, RouteDef> $children
     */
    public static function createDef(
        string $title = '_',
        string $controllerFqcn = UnderscoreController::class,
        string $baseUrl = '_',
        bool $isIndexed = true,
        bool $isPartOfHierarchy = true,
        array $children = [],
    ): RouteDef {
        return new RouteDef(
            new StaticPageConf($title, $controllerFqcn, $baseUrl, $isIndexed, $isPartOfHierarchy),
            children: $children,
        );
    }
}
