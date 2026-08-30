<?php

declare(strict_types=1);

namespace LMWF\Tests\Http\Routing;

use LMWF\Conf\Http\RouteDef;
use LMWF\Http\Routing\Route;
use PHPUnit\Framework\TestCase;
use DomainException;
use LMWF\DataStructures\Page;
use LMWF\ErrorHandling\ExceptionCode;
use LMWF\Http\DataStructures\EntPageConf;
use LMWF\Http\DataStructures\InheritedPageConf;
use LMWF\Http\DataStructures\PageConf;
use LMWF\Http\DataStructures\StaticPageConf;
use LMWF\Http\Factory\PageFactory;
use LMWF\Http\Routing\EntPageTitleFormatter;
use LMWF\Tests\Factory\PageParamFactory;
use LMWF\Tests\Factory\RouteFactory;
use LMWF\Tests\Mocks\ContainerMock;
use LMWF\Tests\Mocks\Controller1;
use LMWF\Tests\Mocks\Controller2;
use LMWF\Tests\Mocks\Controller3;
use LMWF\Tests\Mocks\OkController;
use LMWF\Tests\Mocks\UnderscoreController;
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

    /**
     * Instantiation.
     */

    public function testNOfParamsTooHigh(): void
    {
        $rootRouteDef = new RouteDef([
            1 => PageParamFactory::createStaticConf(),
        ], nParamsLeast: 1, nParamsMax: 2);

        $this->expectException(DomainException::class);
        new Route($rootRouteDef, '', parent: null, params: ['args1', 'args2', 'args3']);
    }


    public function testNOfParamsTooLow(): void
    {
        $rootRouteDef = new RouteDef([
            0 => PageParamFactory::createStaticConf(),
        ]);

        $this->expectException(DomainException::class);
        new Route($rootRouteDef, '', parent: null, params: ['args1']);
    }

    // @todo Test that a route cannot be instantiated with a seg that is not
    // defined by its parent.

    // @todo Check that a route parent is indeed its direct parent in case the
    // route has at least one parameter.

    // @todo Check that there is a conf defined for each parameter.


    /**
     * Test root route behaviour. (A root route is a route with a null parent).
     */


    /**
     * Checks that an exception is thrown if the root route is assigned a
     * non-empty seg.
     */
    public function testRootRouteWithNonEmptySeg(): void
    {
        $this->expectExceptionCode(ExceptionCode::HTTP_ROUTING_ROUTE_ROOT_ROUTE_HAS_NON_EMPTY_SEG->value);
        new Route(
            new RouteDef(),
            'seg',
            parent: null,
        );
    }

    /**
     * Checks that an exception is thrown if the root route has a child route
     * assigned to an empty seg.
     */
    public function testRootRouteWithEmptySegChild(): void
    {
        $this->expectExceptionCode(
            ExceptionCode::HTTP_ROUTING_ROUTE_ROOT_ROUTE_HAS_CHILD_WITH_EMPTY_SEG->value,
        );
        new Route(
            new RouteDef(subRouteDefs: [
                '' => new RouteDef()
            ]),
            seg: '',
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
            new RouteDef([
                0 => PageParamFactory::createStaticConf(),
            ], nParamsMax: 1),
            '',
            parent: null,
        );
    }


    /**
     * Test Paths.
     */


    public function testPathRootRoute(): void
    {
        $rootRouteDef = new RouteDef();
        $rootRoute = new Route($rootRouteDef, '', parent: null);
        self::assertSame('', $rootRoute->getPath());
    }

    public function testNestedRoutes(): void
    {
        $subSubRouteSeg = 'grand-child';
        $subSubRouteDef = new RouteDef(nParamsMax: 1);
        $subRouteSeg = 'child';
        $subRouteDef = new RouteDef(
            subRouteDefs: [
                $subSubRouteSeg => $subSubRouteDef,
            ],
        );
        $rootRouteDef = new RouteDef(
            subRouteDefs: [
                $subRouteSeg => $subRouteDef,
            ],
        );

        $rootRoute = new Route($rootRouteDef, seg: '', parent: null);
        $subRoute = new Route($subRouteDef, $subRouteSeg, $rootRoute);
        $subSubRoute = new Route($subSubRouteDef, $subSubRouteSeg, $subRoute);
        $subSubRouteWithParam = new Route($subSubRouteDef, $subSubRouteSeg, $subRoute, ['param']);

        self::assertSame('', $rootRoute->getPath());

        self::assertSame('/child', $subRoute->getPath());
        self::assertSame($rootRoute, $subRoute->parent);

        self::assertSame('/child/grand-child', $subSubRoute->getPath());
        self::assertSame($subRoute, $subSubRoute->parent);
    }


    /**
     * Pages.
     */

    // @todo Test that an EntPageConf or InheritedPageConf as first param throw exception


    public function testEmptySegWithParams(): void
    {
        $def = new RouteDef([
            0 => $pageConf0Params = PageParamFactory::createStaticConf(
                controllerFqcn: Controller1::class,
                baseUrl: self::BASE_URL,
            ),
            1 => new InheritedPageConf(),
            2 => $pageConf2Params = new EntPageConf(
                UserRepo::class,
                '{{ name }}',
                Controller2::class,
                self::BASE_URL,
                true,
                true,
            ),
            3 => new InheritedPageConf(),
            4 => null,
        ], nParamsMax: 4);
        $parentDef = new RouteDef(null, [
            '' => $def,
        ]);

        $rootRoute = RouteFactory::createRootRoute([
            '_' => $parentDef,
        ]);
        self::assertEquals('', $rootRoute->getPath());

        $parentRoute = new Route($parentDef, '_', $rootRoute);
        self::assertEquals('/_', $parentRoute->getPath());
        
        $route0Params = new Route($def, '', $parentRoute);
        $page0Params = $this->pageFactory->fromStaticPageConf($pageConf0Params, '/_/', null);
        self::assertEquals('/_/', $route0Params->getPath());
        self::assertEquals($page0Params, $this->pageFactory->create($route0Params));
        
        $route1Params = new Route($def, '', $route0Params, ['p1']);
        $page1Params = $this->pageFactory->fromStaticPageConf($pageConf0Params, '/_//p1', $page0Params);
        self::assertEquals('/_//p1', $route1Params->getPath());
        self::assertEquals($page1Params, $this->pageFactory->create($route1Params));
        
        $route2Params = new Route($def, '', $route1Params, ['p1', UserRepo::USER_ID]);
        $page2Params = new Page(
            parent: $page1Params,
            controllerFqcn: $pageConf2Params->getControllerFqcn(),
            name: UserRepo::USER_NAME,
            url: self::BASE_URL . '/_//p1/' . UserRepo::USER_ID,
        );
        self::assertEquals('/_//p1/' . UserRepo::USER_ID, $route2Params->getPath());
        self::assertEquals($page2Params, $this->pageFactory->create($route2Params));
        
        $route3Params = new Route($def, '', $route2Params, ['p1', 'p2', UserRepo::USER_ID]);
        $page3Params = new Page(
            parent: $page2Params,
            controllerFqcn: $pageConf2Params->getControllerFqcn(),
            name: UserRepo::USER_NAME,
            url: self::BASE_URL . '/_//p1/' . UserRepo::USER_ID . '/' . UserRepo::USER_ID,
        );
        self::assertEquals($route2Params->getPath() . '/' . UserRepo::USER_ID, $route3Params->getPath());
        self::assertEquals($page3Params, $this->pageFactory->create($route3Params));
        
        $routeParam4 = new Route($def, '', $route3Params, ['p1', UserRepo::USER_ID, UserRepo::USER_ID, 'p4']);
        self::assertEquals($route3Params->getPath() . '/p4', $routeParam4->getPath());
        self::assertEquals(null, $this->pageFactory->create($routeParam4));
    }

    public function testNestedPages(): void
    {
        $subRouteSeg = 'child';
        $subRoutePageConf = new StaticPageConf(
            'Child Page',
            UnderscoreController::class,
            self::BASE_URL,
            isIndexed: false,
            isInHierarchy: false,
        );
        $rootPageConf = new StaticPageConf(
            'Home Page',
            UnderscoreController::class,
            self::BASE_URL,
            isIndexed: true,
            isInHierarchy: true,
        );
        $rootPage = new Page(
            null,
            $rootPageConf->getControllerFqcn(),
            $rootPageConf->getTitle(),
            $rootPageConf->getBaseUrl(),
            $rootPageConf->isIndexed(),
            $rootPageConf->isInHierarchy(),
        );
        $childPage = new Page(
            $rootPage,
            $subRoutePageConf->getControllerFqcn(),
            $subRoutePageConf->getTitle(),
            self::BASE_URL . "/$subRouteSeg",
            $subRoutePageConf->isIndexed(),
            $subRoutePageConf->isInHierarchy(),
        );
        $rootRouteDef = new RouteDef(
            $rootPageConf,
            [
                $subRouteSeg => new RouteDef($subRoutePageConf),
            ],
        );

        $routeToChild = new Route(
            $rootRouteDef->subRouteDefs[$subRouteSeg],
            $subRouteSeg,
            new Route($rootRouteDef, '', parent: null),
        );

        // Testing the parent before the sub-route as the errors would propagate.
        self::assertEquals($rootPage, $this->pageFactory->create($routeToChild->parent));

        self::assertEquals($childPage, $this->pageFactory->create($routeToChild));
    }
}
