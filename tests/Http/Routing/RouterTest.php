<?php

declare(strict_types=1);

namespace LMWF\Tests\Http\Routing;

use DomainException;
use InvalidArgumentException;
use LMWF\Http\Routing\Exception\RouteNotFoundException;
use LMWF\Http\Routing\Route;
use LMWF\Conf\Http\RouteDef;
use LMWF\Http\Controller\Issue\RouteNotFoundIssue;
use LMWF\Http\Controller\Issue\RoutingParamIssue;
use LMWF\Http\Controller\Issue\RoutingParamIssueCode;
use LMWF\Http\Routing\Router;
use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{
    public function testHomeUrl(): void
    {
        $router = new Router();

        $homeRouteDef = new RouteDef(self::class);
        $rootRoute = Route::createRootRouteDef([
            '' => $homeRouteDef,
        ]);

        $homeRoute = new Route($homeRouteDef, '', [], parent: $rootRoute);

        self::assertEquals($homeRoute, $router->getRouteFromPath($rootRoute->def, ''));
        self::assertEquals($homeRoute, $router->getRouteFromPath($rootRoute->def, '/'));
    }

    public function testRouteIdWithSpecialChars(): void
    {
        $router = new Router();

        $subrouteId = 'c’est mon idée de route !';
        $subrouteDef = new RouteDef(self::class);

        $rootRoute = Route::createRootRouteDef([
            $subrouteId => $subrouteDef,
        ]);
        $subroute = new Route($subrouteDef, $subrouteId, parent: $rootRoute);

        self::assertEquals($subroute, $router->getRouteFromPath($rootRoute->def, "/{$subrouteId}"));
    }

    public function testRootRouteWithZeroMinParams(): void
    {
        $routeDef = new RouteDef(null, [], nArgsUpperLimit: 1);
        $router = new Router();

        $this->expectException(DomainException::class);
        $router->getRouteFromPath($routeDef, '');
    }

    /**
     * If the root route does not define any child, we should get an exception
     * from the router as it makes it impossible for any path to match any
     * route (since a path is at least composed of two segments).
     */
    public function testRootRouteWithZeroParams(): void
    {
        $routeDef = new RouteDef(null, []);
        $router = new Router();

        $this->expectException(DomainException::class);
        $router->getRouteFromPath($routeDef, '');
    }

    public function testRootRouteWithNoChild(): void
    {
        $this->expectException(DomainException::class);
        Route::createRootRouteDef([]);
    }

    public function testRootRouteWithOneParamOnly(): void
    {
        $router = new Router();

        $routeDef = new RouteDef(self::class, nArgsLowerLimit: 1, nArgsUpperLimit: 1);

        self::assertEquals(
            new RoutingParamIssue(RoutingParamIssueCode::TooManyParams, $routeDef, 2),
            $router->getRouteFromPath($routeDef, '/param1/param2'),
        );
        self::assertEquals(
            new RoutingParamIssue(RoutingParamIssueCode::TooManyParams, $routeDef, 2),
            $router->getRouteFromPath($routeDef, '/param1/'),
        );
        self::assertEquals(
            new Route($routeDef, '', ['param1']),
            $router->getRouteFromPath($routeDef, '/param1'),
        );
    }

    /**
     * An exception should be thrown by the parser if the passed path is not a
     * valid absolute path, or an empty string.
     */
    public function testNonAbsolutePath(): void
    {
        $router = new Router();
        $routeDef = new RouteDef(null, nArgsLowerLimit: 1, nArgsUpperLimit: 1);

        $this->expectException(DomainException::class);

        $router->getRouteFromPath($routeDef, 'test');
    }

    public function testNonExistingRoute(): void
    {
        $router = new Router();

        $rootRoute = Route::createRootRouteDef([
            '' => new RouteDef(null, [])
        ]);

        self::assertEquals(
            new RouteNotFoundIssue('test'),
            $router->getRouteFromPath($rootRoute->def, '/test'),
        );
    }

    public function testSubroute(): void
    {
        $sub1SubrouteDef = new RouteDef(self::class);
        $sub2SubrouteDef = new RouteDef(self::class, nArgsUpperLimit: 3);

        $router = new Router();

        $rootRoute = Route::createRootRouteDef([
            'sub1' => $sub1SubrouteDef,
            'sub2' => $sub2SubrouteDef,
        ]);
        $sub1Route = new Route($sub1SubrouteDef, 'sub1', [], $rootRoute);
        $sub2Route = new Route($sub2SubrouteDef, 'sub2', ['param1', 'param2'], $rootRoute);
        $sub2RouteNoParams = new Route($sub2SubrouteDef, 'sub2', [], $rootRoute);

        self::assertEquals($sub1Route, $router->getRouteFromPath($rootRoute->def, '/sub1'));
        self::assertEquals($sub2Route, $router->getRouteFromPath($rootRoute->def, '/sub2/param1/param2'));
        self::assertEquals($sub2RouteNoParams, $router->getRouteFromPath($rootRoute->def, '/sub2'));
    }



    public function testRootRouteWithOneToTwoParams(): void
    {
        $routeDef = new RouteDef(null, nArgsLowerLimit: 1, nArgsUpperLimit: 2);
        $router = new Router();

        self::assertEquals(new Route($routeDef, '', ['']), $router->getRouteFromPath($routeDef, ''));
        self::assertEquals(new Route($routeDef, '', ['']), $router->getRouteFromPath($routeDef, '/'));
        self::assertEquals(new Route($routeDef, '', ['', '']), $router->getRouteFromPath($routeDef, '//'));
        self::assertEquals(new Route($routeDef, '', ['param1']), $router->getRouteFromPath($routeDef, '/param1'));
        self::assertEquals(new Route($routeDef, '', ['param1', '']), $router->getRouteFromPath($routeDef, '/param1/'));
        self::assertEquals(new RoutingParamIssue(RoutingParamIssueCode::TooManyParams, $routeDef, 3), $router->getRouteFromPath($routeDef, '/param1/param2/param3'));
    }
}
