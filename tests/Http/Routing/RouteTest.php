<?php

declare(strict_types=1);

namespace LMWF\Tests\Http\Routing;

use LMWF\Conf\Http\RouteDef;
use LMWF\Http\Routing\Route;
use PHPUnit\Framework\TestCase;
use DomainException;
use LMWF\DataStructures\Page;
use LMWF\Http\DataStructures\PageConf;
use LMWF\Http\Factory\PageFactory;
use LMWF\Http\Routing\EntPageTitleFormatter;
use LMWF\Tests\Factory\PageParamFactory;
use LMWF\Tests\Factory\RouteFactory;
use LMWF\Tests\Mocks\ContainerMock;
use LMWF\Tests\Mocks\ControllerMock;
use LMWF\Tests\Mocks\UserRepo;

final class RouteTest extends TestCase
{
    private PageFactory $pageFactory;

    #[\Override]
    public function setUp(): void
    {
        $this->pageFactory = new PageFactory(
            new EntPageTitleFormatter(
                new ContainerMock([
                    UserRepo::class => new UserRepo(),
                ]),
            ),
        );
    }

    public function testInvalidRootRouteWithSeg(): void
    {
        $this->expectException(DomainException::class);
        new Route(new RouteDef(self::class, PageParamFactory::create()), 'seg');
    }

    public function testInvalidRootRouteWithFqcn(): void
    {
        $this->expectException(DomainException::class);
        new Route(new RouteDef(self::class, PageParamFactory::create()), '');
    }

    public function testInvalidRootRouteWithNoParams(): void
    {
        $this->expectException(DomainException::class);
        new Route(new RouteDef(self::class, PageParamFactory::create()), '');
    }

    public function testInvalidRouteParams(): void
    {
        $rootRouteDef = new RouteDef(null, null, nArgsLowerLimit: 1, nArgsUpperLimit: 2);
        $this->expectException(DomainException::class);
        new Route($rootRouteDef, '', ['args1', 'args2', 'args3']);
    }

    public function testRootRoute(): void
    {
        $homeRouteDef = new RouteDef(self::class, PageParamFactory::create());
        $rootRoute = RouteFactory::createRootRoute(['' => $homeRouteDef]);
        $homeRoute = new Route($homeRouteDef, '', parent: $rootRoute);
        self::assertSame('', $rootRoute->getPath());
        self::assertSame('/', $homeRoute->getPath());
    }

    public function testRootRouteWithParams(): void
    {
        $rootRouteDef = new RouteDef(null, null, nArgsLowerLimit: 1, nArgsUpperLimit: 2);
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
            PageParamFactory::create(),
            nArgsLowerLimit: 1,
            nArgsUpperLimit: 1,
        );
        $rootRoute = RouteFactory::createRootRoute(['' => $homeRouteDef]);
        $homeRoute = new Route($homeRouteDef, '', ['test-param'], parent: $rootRoute);
        self::assertSame('//test-param', $homeRoute->getPath());
    }

    public function testParentRoute(): void
    {
        $subrouteDef = new RouteDef(self::class, PageParamFactory::create());
        $rootRoute = RouteFactory::createRootRoute(['sub' => $subrouteDef]);

        $subroute = new Route($subrouteDef, 'sub', parent: $rootRoute);
        self::assertSame('/sub', $subroute->getPath());
    }

    public function testNestedRoutes(): void
    {
        $subSubrouteDef = new RouteDef(self::class, PageParamFactory::create());
        $subrouteDef = new RouteDef(
            self::class,
            PageParamFactory::create(),
            subroutes: [
                'sub2' => $subSubrouteDef,
            ],
        );
        $rootRoute = RouteFactory::createRootRoute([
            'sub1' => $subrouteDef,
        ]);

        $subroute = new Route($subrouteDef, 'sub1', parent: $rootRoute);
        $subSubroute = new Route($subSubrouteDef, 'sub2', parent: $subroute);
        self::assertSame('/sub1/sub2', $subSubroute->getPath());
    }

    public function testComplexParentRoute(): void
    {

        $sub1RouteDef = new RouteDef(self::class, PageParamFactory::create());
        $subSub2RouteDef = new RouteDef(self::class, PageParamFactory::create());

        $sub2RouteDef = new RouteDef(
            self::class,
            PageParamFactory::create(),
            subroutes: [
                '' => $subSub2RouteDef,
            ],
        );
        $rootRoute = RouteFactory::createRootRoute([
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

    public function testHomeAndRootPage(): void
    {
        /**
         * Constants.
         */
        $childSeg = 'child';
        $homeSeg = 'home';
        $baseUrl = 'https://example.org';

        $homePageParam = new PageConf('Home Page', $baseUrl, true, true);
        $childPageParam = new PageConf('Child Page', $baseUrl, false, false);

        $childRouteDef = new RouteDef(ControllerMock::class, $childPageParam);
        $homeRouteDef = new RouteDef(ControllerMock::class, $homePageParam, subroutes: [
            $childSeg => $childRouteDef,
        ]);

        $rootRoute = RouteFactory::createRootRoute([
            $homeSeg => $homeRouteDef,
        ]);

        self::assertNull($this->pageFactory->getPage($rootRoute));


        $childRoute = new Route(
            $childRouteDef,
            $childSeg,
            parent: new Route($homeRouteDef, $homeSeg, parent: $rootRoute),
        );

        $homePage = new Page(
            null,
            $homePageParam->title,
            "$baseUrl/$homeSeg",
            $homePageParam->isIndexed,
            $homePageParam->isPartOfHierarchy,
        );
        self::assertEquals($homePage, $this->pageFactory->getPage($childRoute->parent));

        $childPage = new Page(
            $homePage,
            $childPageParam->title,
            "$baseUrl/$homeSeg/$childSeg",
            $childPageParam->isIndexed,
            $childPageParam->isPartOfHierarchy,
        );
        self::assertEquals($childPage, $this->pageFactory->getPage($childRoute));
    }
}
