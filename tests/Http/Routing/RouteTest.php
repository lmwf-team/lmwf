<?php

declare(strict_types=1);

namespace LMWF\Tests\Http\Routing;

use LMWF\Http\DataStructures\RouteDef;
use LMWF\Http\Routing\Route;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;
use LMWF\DataStructures\Page;
use LMWF\ErrorHandling\ExceptionCode;
use LMWF\Http\DataStructures\EntPageConf;
use LMWF\Http\DataStructures\StaticPageConf;
use LMWF\Http\Factory\PageFactory;
use LMWF\Http\Routing\EntPageTitleFormatter;
use LMWF\Tests\Factory\PageParamFactory;
use LMWF\Tests\Factory\RouteFactory;
use LMWF\Tests\Mocks\ContainerMock;
use LMWF\Tests\Mocks\Controller1;
use LMWF\Tests\Mocks\Controller2;
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

    public function testConstructorWhenNOfParamsTooHigh(): void
    {
        $rootDef = new RouteDef(children: [
            '_' => new RouteDef(params: [
                0 => PageParamFactory::createStaticConf(),
            ]),
        ]);

        $this->expectException(InvalidArgumentException::class);
        new Route($rootDef, parent: null, seg: '', params: ['seg', 'args1', 'args2', 'args3']);
    }

    public function testConstructorWhenParamsIsNotAList(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Route(RouteFactory::createDef(), parent: null, seg: '', params: [1 => 'args1']);
    }

    // @todo Test that a route cannot be instantiated with a seg that is not
    // defined by its parent.

    // @todo Check that a route parent is indeed its direct parent in case the
    // route has at least one parameter.

    // @todo Check that there is a conf defined for each parameter.

        /**
     * Checks that an exception is thrown if the root route is assigned a
     * non-empty seg.
     */
    public function testConstructorWhenRootHasNonEmptySeg(): void
    {
        $this->expectExceptionCode(ExceptionCode::HTTP_ROUTING_ROUTE_ROOT_ROUTE_HAS_NON_EMPTY_SEG->value);
        new Route(
            new RouteDef(noParamConf: null),
            parent: null,
            seg: 'seg',
        );
    }

    /**
     * Checks that an exception is thrown if the root route has a child route
     * assigned to an empty seg.
     */
    public function testConstructorWhenRootHasEmptySegChild(): void
    {
        $this->expectExceptionCode(
            ExceptionCode::HTTP_ROUTING_ROUTE_ROOT_ROUTE_HAS_CHILD_WITH_EMPTY_SEG->value,
        );
        new Route(
            new RouteDef(children: [
                '' => new RouteDef(noParamConf: null)
            ]),
            seg: '',
            parent: null,
        );
    }

    /**
     * Checks that the root route cannot accept parameters.
     */
    public function testConstructorWhenRootHasParams(): void
    {
        $this->expectExceptionCode(
            ExceptionCode::HTTP_ROUTING_ROUTE_ROOT_ROUTE_ACCEPTS_PARAMS->value,
        );
        new Route(
            new RouteDef(params: [
                0 => PageParamFactory::createStaticConf(),
            ]),
            parent: null,
            seg: '',
        );
    }


    /**
     * Test isEqual.
     */


    public function testEqualityWithTheSameRoute(): void
    {
        $route = new Route(RouteFactory::createDef(), null, '');
        self::assertTrue($route->isEqual($route));
    }

    public function testEqualityWithIdenticalRoute(): void
    {
        $def = RouteFactory::createDef();
        $route1 = new Route($def, null, '');
        $route2 = new Route($def, null, '');
        self::assertTrue($route1->isEqual($route2));
        self::assertTrue($route2->isEqual($route1));
    }

    public function testEqualityWithIdenticalRouteWithParents(): void
    {
        $def = RouteFactory::createDef();
        $rootDef = new RouteDef(null, children: ['_' => $def]);
        $route1 = new Route($def, parent: new Route($rootDef, parent: null, seg: ''), seg: '');
        $route2 = new Route($def, parent: new Route($rootDef, parent: null, seg: ''), seg: '');
        self::assertTrue($route1->isEqual($route2));
        self::assertTrue($route2->isEqual($route1));

        $rootDef = new RouteDef(null, children: ['_' => $def]);
        $route3 = new Route($def, parent: new Route($rootDef, parent: null, seg: ''), seg: '');
        self::assertFalse($route1->isEqual($route3));
        self::assertFalse($route3->isEqual($route1));
    }

    public function testEqualityWithOneNullParent(): void
    {
        $rootDef = RouteFactory::createDef(children: [
            '_' => $def = new RouteDef(children: [
                '' => $subDef = new RouteDef(noParamConf: null),
            ]),
        ]);
        $route1 = new Route($subDef, new Route($def, parent: new Route($rootDef, parent: null, seg: ''), seg: '_'), seg: '');
        $route2 = new Route($subDef, parent: null, seg: '');
        self::assertFalse($route1->isEqual($route2));
        self::assertFalse($route2->isEqual($route1));
    }

    public function testEqualityWithParams(): void
    {
        $rootDef = RouteFactory::createDef(children: [
            '_' => $def = new RouteDef(
                PageParamFactory::createStaticConf(),
                params: [
                    0 => null,
                ]
            )
        ]);
        $route1 = new Route($def, new Route($rootDef, parent: null, seg: ''), '_');
        $route2 = new Route($def, new Route($rootDef, parent: null, seg: ''), '_');
        self::assertTrue($route1->isEqual($route2));
        self::assertTrue($route2->isEqual($route1));
    }

    /**
     * Test root route behaviour. (A root route is a route with a null parent).
     */


    /**
     * Test Paths.
     */


    public function testPathRootRoute(): void
    {
        $rootDef = new RouteDef();
        $rootRoute = new Route($rootDef, parent: null, seg: '');
        self::assertSame('', $rootRoute->getPath());
    }

    public function testNestedRoutes(): void
    {
        $rootDef = new RouteDef(children: [
            'sub' => $subDef = new RouteDef(children: [
                'sub-sub' => $subSubDef = new RouteDef(params: [
                    0 => null,
                ]),
            ]),
        ]);

        $root = new Route($rootDef, parent: null, seg: '');
        $subRoute = new Route($subDef, $root, 'sub');
        $subSubRoute = new Route($subSubDef, $subRoute, 'sub-sub');
        $subSubRouteWithParam = new Route($subSubDef, $subSubRoute, 'sub-sub', ['param']);

        self::assertEquals('', $root->getPath());
        self::assertEquals('/sub', $subRoute->getPath());
        self::assertEquals('/sub/sub-sub', $subSubRoute->getPath());
        self::assertEquals('/sub/sub-sub/param', $subSubRouteWithParam->getPath());
    }


    /**
     * Pages.
     */


    public function testEmptySegWithParams(): void
    {
        $rootRoute = RouteFactory::createRootRoute([
            '_' => $parentDef = new RouteDef(null, children: [
                '' => $def = new RouteDef(
                    PageParamFactory::createStaticConf(
                        controllerFqcn: Controller1::class,
                        baseUrl: self::BASE_URL,
                    ),
                    params: [
                        0 => null,
                        1 => $pageConf2Params = new EntPageConf(
                            UserRepo::class,
                            'Hey {{ name }}!',
                            Controller2::class,
                            self::BASE_URL,
                            true,
                            true,
                        ),
                    ],
                ),
            ]),
        ]);
        self::assertEquals('', $rootRoute->getPath());
        self::assertEquals(null, $this->pageFactory->create($rootRoute));

        $parentRoute = new Route($parentDef, $rootRoute, '_');
        self::assertEquals('/_', $parentRoute->getPath());

        $routeParams0 = new Route($def, $parentRoute, '');
        $pageParams0Expected = $this->pageFactory->fromStaticPageConf($def->noParamConf, '/_/', null);
        self::assertEquals('/_/', $routeParams0->getPath());
        self::assertEquals($pageParams0Expected, $this->pageFactory->create($routeParams0));

        $routeParams1 = new Route($def, $routeParams0, '', ['p1']);
        self::assertEquals('/_//p1', $routeParams1->getPath());
        self::assertNull($this->pageFactory->create($routeParams1));

        $route2Params = new Route($def, $routeParams1, seg: '', params: ['p1', UserRepo::USER_ID]);
        $pageParams2Expected = new Page(
            parent: $pageParams0Expected,
            controllerFqcn: $pageConf2Params->getControllerFqcn(),
            name: 'Hey ' . UserRepo::USER_NAME . '!',
            url: self::BASE_URL . '/_//p1/' . UserRepo::USER_ID,
        );
        self::assertEquals('/_//p1/' . UserRepo::USER_ID, $route2Params->getPath());
        self::assertEquals($pageParams2Expected, $this->pageFactory->create($route2Params));
    }

    public function testNestedPages(): void
    {
        $seg = 'child';
        $pageConf = new StaticPageConf(
            'Child Page',
            Controller1::class,
            self::BASE_URL,
            isIndexed: false,
            isInHierarchy: false,
        );
        $rootPageConf = new StaticPageConf(
            'Home Page',
            Controller2::class,
            self::BASE_URL,
            isIndexed: true,
            isInHierarchy: true,
        );
        $rootDef = new RouteDef(
            $rootPageConf,
            children: [
                $seg => $def = new RouteDef($pageConf),
            ],
        );

        $route = new Route(
            $def,
            new Route($rootDef, parent: null, seg: ''),
            $seg,
        );

        $rootPageExpected = new Page(
            null,
            $rootPageConf->getControllerFqcn(),
            $rootPageConf->getTitle(),
            $rootPageConf->getBaseUrl(),
            $rootPageConf->isIndexed(),
            $rootPageConf->isInHierarchy(),
        );
        $childPageExpected = new Page(
            $rootPageExpected,
            $pageConf->getControllerFqcn(),
            $pageConf->getTitle(),
            self::BASE_URL . "/$seg",
            $pageConf->isIndexed(),
            $pageConf->isInHierarchy(),
        );

        // Testing the parent before the sub-route as the errors would propagate.
        self::assertEquals($rootPageExpected, $this->pageFactory->create($route->parent));

        self::assertEquals($childPageExpected, $this->pageFactory->create($route));
    }
}
