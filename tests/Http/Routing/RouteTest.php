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
        $rootRoute = Route::createRootRouteDef(['' => $homeRouteDef]);
        $homeRoute = new Route($homeRouteDef, '', parent: $rootRoute);
        self::assertSame('', $rootRoute->getPath());
        self::assertSame('/', $homeRoute->getPath());
    }

    public function testRootRouteWithParams(): void
    {
        $rootRouteDef = new RouteDef(null, nArgsLowerLimit: 1, nArgsUpperLimit: 2);
        $rootRouteArgs1 = new Route($rootRouteDef, '', ['']);
        $rootRouteArgs2 = new Route($rootRouteDef, '', ['args2']);
        $rootRouteArgs3 = new Route($rootRouteDef, '', ['args3a', 'args3b']);
        self::assertSame('/', $rootRouteArgs1->getPath());
        self::assertSame('/args2', $rootRouteArgs2->getPath());
        self::assertSame('/args3a/args3b', $rootRouteArgs3->getPath());
    }

    public function testHomeRouteWithParams(): void
    {
        $homeRouteDef = new RouteDef(
            self::class,
            nArgsLowerLimit: 1,
            nArgsUpperLimit: 1,
        );
        $rootRoute = Route::createRootRouteDef(['' => $homeRouteDef]);
        $homeRoute = new Route($homeRouteDef, '', ['test-param'], parent: $rootRoute);
        self::assertSame('//test-param', $homeRoute->getPath());
    }

    public function testParentRoute(): void
    {
        $subrouteDef = new RouteDef(self::class);
        $rootRoute = Route::createRootRouteDef(['sub' => $subrouteDef]);

        $subroute = new Route($subrouteDef, 'sub', parent: $rootRoute);
        self::assertSame('/sub', $subroute->getPath());
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
        $rootRoute = Route::createRootRouteDef([
            'sub1' => $subrouteDef,
        ]);

        $subroute = new Route($subrouteDef, 'sub1', parent: $rootRoute);
        $subSubroute = new Route($subSubrouteDef, 'sub2', parent: $subroute);
        self::assertSame('/sub1/sub2', $subSubroute->getPath());
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
        $rootRoute = Route::createRootRouteDef([
            '' => $sub1RouteDef,
            'sub2' => $sub2RouteDef,
        ]);

        $sub1Route = new Route($sub1RouteDef, '', parent: $rootRoute);
        $sub2Route = new Route($sub2RouteDef, 'sub2', parent: $rootRoute);
        $subSub2Route = new Route($subSub2RouteDef, '', parent: $sub2Route);
        self::assertSame('', $rootRoute->getPath());
        self::assertSame('/', $sub1Route->getPath());
        self::assertSame('/sub2', $sub2Route->getPath());
        self::assertSame('/sub2/', $subSub2Route->getPath());
    }
}
