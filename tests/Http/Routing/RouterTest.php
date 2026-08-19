<?php

declare(strict_types=1);

namespace LMWF\Tests\Http\Routing;

use DomainException;
use LMWF\Http\Routing\Route;
use LMWF\Conf\Http\RouteDef;
use LMWF\DataStructures\Page;
use LMWF\Http\DataStructures\PageConf;
use LMWF\Http\Controller\Issue\RouteNotFoundIssue;
use LMWF\Http\Controller\Issue\RoutingParamIssue;
use LMWF\Http\Controller\Issue\RoutingParamIssueCode;
use LMWF\Http\DataStructures\PageEntConf;
use LMWF\Http\Factory\PageEntTitleErr;
use LMWF\Http\Factory\PageFactory;
use LMWF\Http\Routing\EntPageTitleFormatter;
use LMWF\Http\Routing\FormatErr;
use LMWF\Http\Routing\Router;
use LMWF\Tests\Factory\PageParamFactory;
use LMWF\Tests\Factory\RouteFactory;
use LMWF\Tests\Mocks\ContainerMock;
use LMWF\Tests\Mocks\OkController;
use LMWF\Tests\Mocks\UserRepo;
use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{
    const string BASE_URL = 'example.org';

    private Router $router;
    private PageFactory $pageFactory;

    #[\Override]
    public function setUp(): void
    {
        $this->router = new Router();
        $this->pageFactory = new PageFactory(
            new EntPageTitleFormatter(
                new ContainerMock([
                    UserRepo::class => new UserRepo(),
                ]),
            ),
        );
    }

    public function testBaseUrl(): void
    {
        $pageParam = PageParamFactory::create();

        $homeRouteDef = new RouteDef(self::class, $pageParam);
        $rootRoute = RouteFactory::createRootRoute([
            '' => $homeRouteDef,
        ]);

        $homeRoute = new Route($homeRouteDef, '', [], parent: $rootRoute);

        self::assertEquals($homeRoute, $this->router->getRouteFromPath($rootRoute->def, ''));
        self::assertEquals($homeRoute, $this->router->getRouteFromPath($rootRoute->def, '/'));
    }

    public function testWithEntConf(): void
    {
        $rootRoute = RouteFactory::createRootRoute([
            '' => new RouteDef(
                OkController::class,
                new PageConf(
                    'Users',
                    self::BASE_URL,
                    entConf: new PageEntConf(
                        'User: {{ name }}',
                        UserRepo::class,
                    ),
                ),
                nArgsUpperLimit: 1,
            ),
        ]);

        self::assertEquals(
            new Page(null, 'Users', url: self::BASE_URL),
            $this->pageFactory->getPage($this->router->getRouteFromPath($rootRoute->def, "/")),
        );

        self::assertEquals(
            new Page(null, 'User: ' . UserRepo::USER_NAME, self::BASE_URL . '//' . UserRepo::USER_ID),
            $this->pageFactory->getPage($this->router->getRouteFromPath($rootRoute->def, '//' . UserRepo::USER_ID)),
        );

        self::assertEquals(
            new PageEntTitleErr(FormatErr::EntNotFound),
            $this->pageFactory->getPage($this->router->getRouteFromPath($rootRoute->def, '//typo-' . UserRepo::USER_ID)),
        );
    }

    /**
     * An exception should be thrown by the parser if the passed path is not a
     * valid absolute path, or an empty string.
     */
    public function testNonAbsolutePath(): void
    {
        $routeDef = new RouteDef(null, null, nArgsLowerLimit: 1, nArgsUpperLimit: 1);

        $this->expectException(DomainException::class);

        $this->router->getRouteFromPath($routeDef, 'test');
    }

    public function testNonExistingRoute(): void
    {

        $rootRoute = RouteFactory::createRootRoute([
            '' => new RouteDef(null, null, [])
        ]);

        self::assertEquals(
            new RouteNotFoundIssue('test'),
            $this->router->getRouteFromPath($rootRoute->def, '/test'),
        );
    }

    public function testRootRouteWithOneToTwoParams(): void
    {
        $routeDef = new RouteDef(null, null, nArgsLowerLimit: 1, nArgsUpperLimit: 2);

        self::assertEquals(new Route($routeDef, '', ['']), $this->router->getRouteFromPath($routeDef, ''));
        self::assertEquals(new Route($routeDef, '', ['']), $this->router->getRouteFromPath($routeDef, '/'));
        self::assertEquals(new Route($routeDef, '', ['', '']), $this->router->getRouteFromPath($routeDef, '//'));
        self::assertEquals(new Route($routeDef, '', ['param1']), $this->router->getRouteFromPath($routeDef, '/param1'));
        self::assertEquals(new Route($routeDef, '', ['param1', '']), $this->router->getRouteFromPath($routeDef, '/param1/'));
        self::assertEquals(new RoutingParamIssue(RoutingParamIssueCode::TooManyParams, $routeDef, 3), $this->router->getRouteFromPath($routeDef, '/param1/param2/param3'));
    }

    public function testRouteIdWithSpecialChars(): void
    {
        $pageParam = PageParamFactory::create();

        $subrouteId = 'c’est mon idée de route !';
        $subrouteDef = new RouteDef(self::class, $pageParam);

        $rootRoute = RouteFactory::createRootRoute([
            $subrouteId => $subrouteDef,
        ]);
        $subroute = new Route($subrouteDef, $subrouteId, parent: $rootRoute);

        self::assertEquals($subroute, $this->router->getRouteFromPath($rootRoute->def, "/{$subrouteId}"));
    }

    public function testRootRouteWithZeroMinParams(): void
    {

        $routeDef = new RouteDef(null, null, [], nArgsUpperLimit: 1);

        $this->expectException(DomainException::class);
        $this->router->getRouteFromPath($routeDef, '');
    }

    /**
     * If the root route does not define any child, we should get an exception
     * from the router as it makes it impossible for any path to match any
     * route (since a path is at least composed of two segments).
     */
    public function testRootRouteWithZeroParams(): void
    {

        $routeDef = new RouteDef(null, null, []);

        $this->expectException(DomainException::class);
        $this->router->getRouteFromPath($routeDef, '');
    }

    public function testRootRouteWithOneParamOnly(): void
    {
        $routeParam = PageParamFactory::create();

        $routeDef = new RouteDef(
            self::class,
            $routeParam,
            nArgsLowerLimit: 1,
            nArgsUpperLimit: 1,
        );

        self::assertEquals(
            new RoutingParamIssue(RoutingParamIssueCode::TooManyParams, $routeDef, 2),
            $this->router->getRouteFromPath($routeDef, '/param1/param2'),
        );
        self::assertEquals(
            new RoutingParamIssue(RoutingParamIssueCode::TooManyParams, $routeDef, 2),
            $this->router->getRouteFromPath($routeDef, '/param1/'),
        );
        self::assertEquals(
            new Route($routeDef, '', ['param1']),
            $this->router->getRouteFromPath($routeDef, '/param1'),
        );
    }

    public function testSubroute(): void
    {
        $pageParam = PageParamFactory::create();

        $sub1SubrouteDef = new RouteDef(self::class, $pageParam);
        $sub2SubrouteDef = new RouteDef(self::class, $pageParam, nArgsUpperLimit: 3);

        $rootRoute = RouteFactory::createRootRoute([
            'sub1' => $sub1SubrouteDef,
            'sub2' => $sub2SubrouteDef,
        ]);
        $sub1Route = new Route($sub1SubrouteDef, 'sub1', [], $rootRoute);
        $sub2Route = new Route($sub2SubrouteDef, 'sub2', ['param1', 'param2'], $rootRoute);
        $sub2RouteNoParams = new Route($sub2SubrouteDef, 'sub2', [], $rootRoute);

        self::assertEquals($sub1Route, $this->router->getRouteFromPath($rootRoute->def, '/sub1'));
        self::assertEquals($sub2Route, $this->router->getRouteFromPath($rootRoute->def, '/sub2/param1/param2'));
        self::assertEquals($sub2RouteNoParams, $this->router->getRouteFromPath($rootRoute->def, '/sub2'));
    }
}
