<?php

declare(strict_types=1);

namespace LMWF\Tests\Http\Routing;

use LMWF\Conf\Http\RouteDef;
use LMWF\Http\Routing\Route;
use PHPUnit\Framework\TestCase;
use DomainException;
use LMWF\DataStructures\Page;
use LMWF\ErrorHandling\ExceptionCode;
use LMWF\Http\DataStructures\PageConf;
use LMWF\Http\Factory\PageFactory;
use LMWF\Http\Routing\EntPageTitleFormatter;
use LMWF\Tests\Factory\PageParamFactory;
use LMWF\Tests\Factory\RouteFactory;
use LMWF\Tests\Mocks\ContainerMock;
use LMWF\Tests\Mocks\ControllerMock;
use LMWF\Tests\Mocks\OkController;
use LMWF\Tests\Mocks\UserRepo;

final class RouteTest extends TestCase
{
    const BASE_URL = 'https://example.org';

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

    public function testInvalidRouteParams(): void
    {
        $rootRouteDef = new RouteDef(null, null, nArgsLowerLimit: 1, nArgsUpperLimit: 2);
        $this->expectException(DomainException::class);
        new Route($rootRouteDef, '', parent: null, params: ['args1', 'args2', 'args3']);
    }

    /**
     * Checks that an exception is thrown if the root route is assigned a
     * non-empty seg.
     */
    public function testRootRouteWithNonEmptySeg(): void
    {
        $this->expectExceptionCode(ExceptionCode::HTTP_ROUTING_ROUTE_ROOT_ROUTE_HAS_NON_EMPTY_SEG->value);
        new Route(
            new RouteDef(self::class, PageParamFactory::create()),
            'seg',
            parent: null,
        );
    }

    /**
     * Checks that an exception is thrown if the root route has a child route
     * assigned to an empty seg, and yet defines a controller FQCN.
     */
    public function testRootRouteWithEmptySegChild(): void
    {
        $this->expectExceptionCode(
            ExceptionCode::HTTP_ROUTING_ROUTE_ROOT_ROUTE_HAS_CHILD_WITH_EMPTY_SEG->value,
        );
        new Route(
            new RouteDef(self::class, PageParamFactory::create(), subRouteDefs: [
                '' => new RouteDef(null, null)
            ]),
            '',
            parent: null,
        );
    }

    /**
     * Checks that the root route cannot accept parameters.
     */
    public function testRootRouteWithParams(): void
    {
        $this->expectExceptionCode(
            ExceptionCode::HTTP_ROUTING_ROUTE_ROOT_ROUTE_ACCEPTS_PARAMS->value,
        );
        new Route(
            new RouteDef(self::class, PageParamFactory::create(), nArgsUpperLimit: 1),
            '',
            parent: null,
        );
    }

    public function testRootRoute(): void
    {
        $homeRouteDef = new RouteDef(self::class, PageParamFactory::create());
        $homeRoute = new Route($homeRouteDef, '', parent: null);
        self::assertSame('/', $homeRoute->getPath());
    }

    public function testNestedRoutes(): void
    {
        $subRouteSeg = 'sub2';

        $subRouteDef = new RouteDef(self::class, PageParamFactory::create());
        $rootRouteDef = new RouteDef(
            self::class,
            PageParamFactory::create(),
            subRouteDefs: [
                $subRouteSeg => $subRouteDef,
            ],
        );

        $rootRoute = new Route($rootRouteDef, '', parent: null);
        $subRoute = new Route($subRouteDef, $subRouteSeg, $rootRoute);

        self::assertSame("/$subRouteSeg", $subRoute->getPath());
    }

    public function testPagesOfRootRouteAndChild(): void
    {
        $childSeg = 'child';

        $rootPageConf = new PageConf('Home Page', self::BASE_URL, true, true);
        $childPageParam = new PageConf('Child Page', self::BASE_URL, false, false);

        $rootRouteDef = new RouteDef(ControllerMock::class, $rootPageConf, subRouteDefs: [
            $childSeg => new RouteDef(ControllerMock::class, $childPageParam),
        ]);

        $routeToChild = new Route(
            $rootRouteDef->subRouteDefs[$childSeg],
            $childSeg,
            new Route($rootRouteDef, '', parent: null),
        );

        $homePage = new Page(
            null,
            $rootPageConf->title,
            self::BASE_URL,
            $rootPageConf->isIndexed,
            $rootPageConf->isPartOfHierarchy,
        );
        self::assertEquals($homePage, $this->pageFactory->getPage($routeToChild->parent));

        $childPage = new Page(
            $homePage,
            $childPageParam->title,
            self::BASE_URL . "/$childSeg",
            $childPageParam->isIndexed,
            $childPageParam->isPartOfHierarchy,
        );
        self::assertEquals($childPage, $this->pageFactory->getPage($routeToChild));
    }

    public function testParentRoute(): void
    {
        $subRouteSeg = 'sub';

        $subRouteDef = new RouteDef(self::class, PageParamFactory::create());
        $rootRouteDef = new RouteDef(
            self::class,
            PageParamFactory::create(),
            subRouteDefs: [
                $subRouteSeg => $subRouteDef,
            ],
        );

        $subroute = new Route($subRouteDef, $subRouteSeg, new Route(
            $rootRouteDef,
            '',
            parent: null,
        ));
        self::assertSame('/sub', $subroute->getPath());
        self::assertSame('/', $subroute->parent?->getPath());
    }

    public function testParentRouteComplex(): void
    {
        $grandChildRouteDef = new RouteDef(
            self::class,
            PageParamFactory::create(),
        );
        $childRouteDef = new RouteDef(
            self::class,
            PageParamFactory::create(),
            subRouteDefs: [
                'grand-child' => $grandChildRouteDef,
            ],
        );
        $rootRouteDef = new RouteDef(
            self::class,
            PageParamFactory::create(),
            subRouteDefs: [
                'child' => $childRouteDef,
            ],
        );

        $rootRoute = new Route($rootRouteDef, '', null);
        $childRoute = new Route($childRouteDef, 'child', $rootRoute);
        $grandChildRoute = new Route($grandChildRouteDef, 'grand-child', $childRoute);
        self::assertSame('/', $rootRoute->getPath());
        self::assertSame('/child', $childRoute->getPath());
        self::assertSame('/child/grand-child', $grandChildRoute->getPath());
    }
}
