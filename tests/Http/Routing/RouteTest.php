<?php

declare(strict_types=1);

namespace LM\WebFramework\Tests\Http\Routing;

use InvalidArgumentException;
use LM\WebFramework\Http\Routing\ParameterizedRoute;
use LM\WebFramework\Http\Routing\ParentRoute;
use LM\WebFramework\Http\Routing\Route;
use PHPUnit\Framework\TestCase;

final class RouteTest extends TestCase
{
    public function testRootRoute(): void
    {
        $routeDef = new ParentRoute(self::class);
        $route = new Route($routeDef, ['']);
        $this->assertSame('/', $route->getPath());
    }

    public function testRoute(): void
    {
        $subRouteDef = new ParentRoute(self::class,);
        $routeDef = new ParentRoute(self::class, routes: [
            'sub' => $subRouteDef,
        ]);
        $route = new Route($routeDef, ['']);
        $subRoute = new Route($subRouteDef, ['sub'], $route);
        $this->assertSame('/sub', $subRoute->getPath());
    }

    public function testNestedRoutes(): void
    {
        $subSubRouteDef = new ParentRoute(self::class,);
        $subRouteDef = new ParentRoute(self::class, routes: [
            'sub2' => $subSubRouteDef,
        ]);
        $routeDef = new ParentRoute(self::class, routes: [
            'sub1' => $subRouteDef,
        ]);
        $route = new Route($routeDef, ['']);
        $subRoute = new Route($subRouteDef, ['sub1'], $route);
        $subSubRoute = new Route($subSubRouteDef, ['sub2'], $subRoute);
        $this->assertSame('/sub1/sub2', $subSubRoute->getPath());
    }

    public function testChildRouteWithNoSeg(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $subRouteDef = new ParentRoute(self::class,);
        $routeDef = new ParentRoute(self::class, routes: [
            'sub' => $subRouteDef,
        ]);
        $route = new Route($routeDef, []);
        $subRoute = new Route($subRouteDef, [], $route);
        $this->assertSame('/sub', $subRoute->getPath());
    }
}
