<?php

declare(strict_types=1);

namespace LM\WebFramework\Tests\Http\Routing;

use DomainException;
use LM\WebFramework\Http\Routing\Exception\RootRouteWithDefaultControllerException;
use LM\WebFramework\Http\Routing\Exception\RouteNotFoundException;
use LM\WebFramework\Http\Routing\Route;
use LM\WebFramework\Http\Routing\RouteDef;
use LM\WebFramework\Http\Routing\Router;
use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{
    public function testHomeUrl(): void
    {
        $router = new Router();

        $homeRouteDef = new RouteDef(self::class);
        $rootRoute = Route::createRootRoute([
            '' => $homeRouteDef,
        ]);

        $homeRoute = new Route($homeRouteDef, '', [], parent: $rootRoute);

        $this->assertEquals($homeRoute, $router->getRouteFromPath($rootRoute->def, ''));
        $this->assertEquals($homeRoute, $router->getRouteFromPath($rootRoute->def, '/'));
    }

    public function testRouteIdWithSpecialChars(): void
    {
        $router = new Router();

        $subrouteId = 'c’est mon idée de route !';
        $subrouteDef = new RouteDef(self::class);

        $rootRoute = Route::createRootRoute([
            $subrouteId => $subrouteDef,
        ]);
        $subroute = new Route($subrouteDef, $subrouteId, parent: $rootRoute);

        $this->assertEquals($subroute, $router->getRouteFromPath($rootRoute->def, "/{$subrouteId}"));
    }

    public function testParameterizedRouteWithBadParams0(): void
    {
        $router = new Router();

        $routeDef = new RouteDef(null, []);

        $this->expectException(RouteNotFoundException::class);
        $router->getRouteFromPath($routeDef, '');
    }

    public function testParameterizedRouteWithBadParams2(): void
    {
        $router = new Router();

        $routeDef = new RouteDef(null, nArgsLowerLimit: 1, nArgsUpperLimit: 2);

        $this->assertEquals(new Route($routeDef, '', ['', '']), $router->getRouteFromPath($routeDef, '//'));
        $this->assertEquals(new Route($routeDef, '', ['test']), $router->getRouteFromPath($routeDef, '/test'));
        $this->expectException(RouteNotFoundException::class);
        $router->getRouteFromPath($routeDef, '///');
    }

    public function testParameterizedRouteWithBadParams4(): void
    {
        $router = new Router();

        $routeDef = new RouteDef(self::class, nArgsLowerLimit: 1, nArgsUpperLimit: 1);

        $this->expectException(RouteNotFoundException::class);
        $router->getRouteFromPath($routeDef, '/test/prout');
    }

    public function testParameterizedRouteWithBadParams5(): void
    {
        $router = new Router();

        $routeDef = new RouteDef(self::class, nArgsLowerLimit: 1, nArgsUpperLimit: 1);

        $this->expectException(RouteNotFoundException::class);
        $router->getRouteFromPath($routeDef, '//test/prout');
    }

    public function testParameterizedRouteWithBadParams6(): void
    {
        $router = new Router();

        $routeDef = new RouteDef(self::class, nArgsLowerLimit: 1, nArgsUpperLimit: 1);

        $this->expectException(RouteNotFoundException::class);
        $router->getRouteFromPath($routeDef, '/test/prout/');
    }

    public function testParameterizedRouteWithBadParams7(): void
    {
        $router = new Router();

        $routeDef = new RouteDef(self::class, nArgsLowerLimit: 1, nArgsUpperLimit: 1);

        $this->expectException(RouteNotFoundException::class);
        $router->getRouteFromPath($routeDef, '/test/prout//');
    }

    public function testNonAbsolutePath(): void
    {
        $router = new Router();

        $rootRoute = Route::createRootRoute([]);

        $this->expectException(DomainException::class);
        $router->getRouteFromPath($rootRoute->def, 'test');
    }

    public function testNonExistingRoute(): void
    {
        $router = new Router();

        $rootRoute = Route::createRootRoute([]);

        $this->expectException(RouteNotFoundException::class);
        $router->getRouteFromPath($rootRoute->def, '/test');
    }

    public function testSubroute(): void
    {
        $sub1SubrouteDef = new RouteDef(self::class);
        $sub2SubrouteDef = new RouteDef(self::class, nArgsUpperLimit: 3);

        $router = new Router();

        $rootRoute = Route::createRootRoute([
            'sub1' => $sub1SubrouteDef,
            'sub2' => $sub2SubrouteDef,
        ]);
        $sub1Route = new Route($sub1SubrouteDef, 'sub1', [], $rootRoute);
        $sub2Route = new Route($sub2SubrouteDef, 'sub2', ['param1', 'param2'], $rootRoute);
        $sub2RouteNoParams = new Route($sub2SubrouteDef, 'sub2', [], $rootRoute);

        $this->assertEquals($sub1Route, $router->getRouteFromPath($rootRoute->def, '/sub1'));
        $this->assertEquals($sub2Route, $router->getRouteFromPath($rootRoute->def, '/sub2/param1/param2'));
        $this->assertEquals($sub2RouteNoParams, $router->getRouteFromPath($rootRoute->def, '/sub2'));
    }
}
