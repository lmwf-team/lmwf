<?php

declare(strict_types=1);

namespace LM\WebFramework\Tests\Http\Routing;

use InvalidArgumentException;
use LM\WebFramework\Http\Routing\RouteDef;
use LM\WebFramework\Http\Routing\Route;
use PHPUnit\Framework\TestCase;
use DomainException;

final class RouteTest extends TestCase
{
    public function testInvalidRootRouteWithSeg(): void
    {
        $this->expectException(DomainException::class);
        new Route(new RouteDef(self::class), 'seg');
    }

    public function testInvalidRootRouteWithFqcn(): void
    {
        $this->expectException(DomainException::class);
        new Route(new RouteDef(self::class), '');
    }

    public function testInvalidRootRouteWithNoParams(): void
    {
        $this->expectException(DomainException::class);
        new Route(new RouteDef(self::class), '');
    }

    public function testInvalidRouteParams(): void
    {
        $rootRouteDef = new RouteDef(null, nArgsLowerLimit: 1, nArgsUpperLimit: 2);
        $this->expectException(DomainException::class);
        $rootRouteArgs4 = new Route($rootRouteDef, '', ['args1', 'args2', 'args3']);
    }

    public function testRootRoute(): void
    {
        $homeRouteDef = new RouteDef(self::class);
        $rootRoute = Route::createRootRoute(['' => $homeRouteDef]);
        $homeRoute = new Route($homeRouteDef, '', parent: $rootRoute);
        $this->assertSame('', $rootRoute->getPath());
        $this->assertSame('/', $homeRoute->getPath());
    }

    public function testRootRouteWithParams(): void
    {
        $rootRouteDef = new RouteDef(null, nArgsLowerLimit: 1, nArgsUpperLimit: 2);
        $rootRouteArgs1 = new Route($rootRouteDef, '', ['']);
        $rootRouteArgs2 = new Route($rootRouteDef, '', ['args2']);
        $rootRouteArgs3 = new Route($rootRouteDef, '', ['args3a', 'args3b']);
        $this->assertSame('/', $rootRouteArgs1->getPath());
        $this->assertSame('/args2', $rootRouteArgs2->getPath());
        $this->assertSame('/args3a/args3b', $rootRouteArgs3->getPath());
    }

    public function testHomeRouteWithParams(): void
    {
        $homeRouteDef = new RouteDef(
            self::class,
            nArgsLowerLimit: 1,
            nArgsUpperLimit: 1,
        );
        $rootRoute = Route::createRootRoute(['' => $homeRouteDef]);
        $homeRoute = new Route($homeRouteDef, '', ['test-param'], parent: $rootRoute);
        $this->assertSame('//test-param', $homeRoute->getPath());
    }

    public function testParentRoute(): void
    {
        $subrouteDef = new RouteDef(self::class);
        $rootRoute = Route::createRootRoute(['sub' => $subrouteDef]);

        $subroute = new Route($subrouteDef, 'sub', parent: $rootRoute);
        $this->assertSame('/sub', $subroute->getPath());
    }

    public function testNestedRoutes(): void
    {
        $subSubrouteDef = new RouteDef(self::class);
        $subrouteDef = new RouteDef(
            self::class,
            subroutes: [
                'sub2' => $subSubrouteDef,
            ],
        );
        $rootRoute = Route::createRootRoute([
            'sub1' => $subrouteDef,
        ]);

        $subroute = new Route($subrouteDef, 'sub1', parent: $rootRoute);
        $subSubroute = new Route($subSubrouteDef, 'sub2', parent: $subroute);
        $this->assertSame('/sub1/sub2', $subSubroute->getPath());
    }

    public function testComplexParentRoute(): void
    {

        $sub1RouteDef = new RouteDef(self::class);
        $subSub2RouteDef = new RouteDef(self::class);

        $sub2RouteDef = new RouteDef(
            self::class,
            subroutes: [
                '' => $subSub2RouteDef,
            ],
        );
        $rootRoute = Route::createRootRoute([
            '' => $sub1RouteDef,
            'sub2' => $sub2RouteDef,
        ]);

        $sub1Route = new Route($sub1RouteDef, '', parent: $rootRoute);
        $sub2Route = new Route($sub2RouteDef, 'sub2', parent: $rootRoute);
        $subSub2Route = new Route($subSub2RouteDef, '', parent: $sub2Route);
        $this->assertSame('', $rootRoute->getPath());
        $this->assertSame('/', $sub1Route->getPath());
        $this->assertSame('/sub2', $sub2Route->getPath());
        $this->assertSame('/sub2/', $subSub2Route->getPath());
    }
}
