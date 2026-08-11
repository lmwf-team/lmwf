<?php

declare(strict_types=1);

namespace LM\WebFramework\Tests\Http\Routing;

use InvalidArgumentException;
use LM\WebFramework\Http\Routing\RouteDef;
use LM\WebFramework\Http\Routing\RouteConf\ParamRouteConf;
use LM\WebFramework\Http\Routing\RouteConf\ParentRouteConf;
use LM\WebFramework\Http\Routing\Route;
use PHPUnit\Framework\TestCase;
use DomainException;

final class RouteTest extends TestCase
{
    public function testInvalidRootRouteWithSeg(): void
    {
        $this->expectException(DomainException::class);
        new Route(new RouteDef(self::class), 'home');
    }

    public function testRootRoute(): void
    {
        $routeDef = new RouteDef(self::class);
        $route = new Route($routeDef, '');
        $this->assertSame('/', $route->getPath());
    }

    public function testRootRouteWithParams(): void
    {
        $routeDef = new RouteDef(
            self::class,
            conf: new ParamRouteConf(nArgsLowerLimit: 1, nArgsUpperLimit: 1),
        );
        $route = new Route($routeDef, '', ['test-param']);
        $this->assertSame('/test-param', $route->getPath());
    }

    public function testParentRoute(): void
    {
        $subRouteDef = new RouteDef(self::class);
        $routeDef = new RouteDef(
            self::class,
            conf: new ParentRouteConf([
                'sub' => $subRouteDef,
            ]),
        );
        $route = new Route($routeDef, '');
        $subRoute = new Route($subRouteDef, 'sub', parent: $route);
        $this->assertSame('/sub', $subRoute->getPath());
    }

    public function testNestedRoutes(): void
    {
        $subSubRouteDef = new RouteDef(self::class);
        $subRouteDef = new RouteDef(
            self::class,
            conf: new ParentRouteConf([
                'sub2' => $subSubRouteDef,
            ]),
        );
        $routeDef = new RouteDef(
            self::class,
            conf: new ParentRouteConf([
                'sub1' => $subRouteDef,
            ]),
        );
        $route = new Route($routeDef, '');
        $subRoute = new Route($subRouteDef, 'sub1', parent: $route);
        $subSubRoute = new Route($subSubRouteDef, 'sub2', parent: $subRoute);
        $this->assertSame('/sub1/sub2', $subSubRoute->getPath());
    }

    public function testComplexParentRoute(): void
    {

        $sub1RouteDef = new RouteDef(self::class);
        $subSub2RouteDef = new RouteDef(self::class);

        $sub2RouteDef = new RouteDef(
            self::class,
            conf: new ParentRouteConf([
                '' => $subSub2RouteDef,
            ]),
        );
        $routeDef = new RouteDef(
            self::class,
            conf: new ParentRouteConf([
                '' => $sub1RouteDef,
                'sub2' => $sub2RouteDef,
            ]),
        );

        $route = new Route($routeDef, '');
        $sub1Route = new Route($sub1RouteDef, '', parent: $route);
        $sub2Route = new Route($sub2RouteDef, 'sub2', parent: $route);
        $subSub2Route = new Route($subSub2RouteDef, '', parent: $sub2Route);
        $this->assertSame('/', $route->getPath());
        $this->assertSame('//', $sub1Route->getPath());
        $this->assertSame('/sub2', $sub2Route->getPath());
        $this->assertSame('/sub2/', $subSub2Route->getPath());
    }
}
