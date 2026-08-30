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
use LMWF\Http\DataStructures\EntPageConf;
use LMWF\Http\DataStructures\StaticPageConf;
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

    public function testEmptyPath(): void
    {
        $rootRoute = RouteFactory::createRootRoute([]);

        self::assertEquals($rootRoute, $this->router->getRouteFromPath($rootRoute->def, ''));
        self::assertEquals($rootRoute, $this->router->getRouteFromPath($rootRoute->def, '/'));
    }

    /**
     * An exception should be thrown by the parser if the passed path is not a
     * valid absolute path, or an empty string.
     */
    public function testNonAbsolutePath(): void
    {
        $route = RouteFactory::createRootRoute();

        $this->expectException(DomainException::class);

        $this->router->getRouteFromPath($route->def, 'test');
    }

    public function testNonExistingRoute(): void
    {

        $rootRoute = RouteFactory::createRootRoute([
            '_' => new RouteDef()
        ]);

        self::assertEquals(
            new RouteNotFoundIssue('test'),
            $this->router->getRouteFromPath($rootRoute->def, '/test'),
        );
    }

    public function testNOfParamsTooHigh(): void
    {

        $rootRoute = RouteFactory::createRootRoute();

        self::assertEquals(
            new RoutingParamIssue(RoutingParamIssueCode::TooManyParams, $rootRoute->def, 1),
            $this->router->getRouteFromPath($rootRoute->def, '/_'),
        );
    }

    public function testNOfParamsTooLow(): void
    {
        $routeDef = new RouteDef([1 => PageParamFactory::createStaticConf()], nParamsLeast: 1, nParamsMax: 1);
        $rootRoute = RouteFactory::createRootRoute([
            '_' => $routeDef,
        ]);

        self::assertEquals(
            new RoutingParamIssue(RoutingParamIssueCode::NotEnoughParams, $routeDef, 0),
            $this->router->getRouteFromPath($rootRoute->def, '/_'),
        );
    }

    public function testRouteIdWithSpecialChars(): void
    {
        $subRouteId = 'c’est mon idée de route !';
        $subRouteDef = RouteFactory::createDef();
        $rootRoute = RouteFactory::createRootRoute([
            $subRouteId => $subRouteDef,
        ]);

        $expectedRoute = new Route($subRouteDef, $subRouteId, $rootRoute);

        $actualRoute = $this->router->getRouteFromPath($rootRoute->def, "/{$subRouteId}");

        self::assertEquals($expectedRoute, $actualRoute);
    }

    public function testSubRoutes(): void
    {
        $sub1Seg = 'sub1';
        $sub2Seg = 'sub2';
        $sub1SubRouteDef = RouteFactory::createDef();
        $sub2SubRouteDef = RouteFactory::createDef(nParamsMax: 3);
        $rootRoute = RouteFactory::createRootRoute([
            $sub1Seg => $sub1SubRouteDef,
            $sub2Seg => $sub2SubRouteDef,
        ]);

        $sub1Route = new Route($sub1SubRouteDef, $sub1Seg, $rootRoute);
        self::assertEquals($sub1Route, $this->router->getRouteFromPath($rootRoute->def, "/$sub1Seg"));

        $sub2Route0Param = new Route($sub2SubRouteDef, $sub2Seg, $rootRoute);
        self::assertEquals($sub2Route0Param, $this->router->getRouteFromPath($rootRoute->def, "/$sub2Seg"));

        $sub2Route1Param = new Route($sub2SubRouteDef, $sub2Seg, $sub2Route0Param, ['param1']);
        $sub2Route2Params = new Route($sub2SubRouteDef, $sub2Seg, $sub2Route1Param, ['param1', 'param2']);
        self::assertEquals($sub2Route2Params, $this->router->getRouteFromPath($rootRoute->def, "/$sub2Seg/param1/param2"));
    }

    public function testWithMultipleParams(): void
    {
        $routeDef = new RouteDef(nParamsMax: 3);

        $rootRoute = RouteFactory::createRootRoute([
            '_' => $routeDef,
        ]);

        $expectedRoute = new Route($routeDef, '_', parent: $rootRoute);
        $expectedSubRoute = new Route($routeDef, '_', $expectedRoute, ['1']);
        $expectedSubSubRoute = new Route($routeDef, '_', $expectedSubRoute, ['1', '2']);

        $actualRoute = $this->router->getRouteFromPath($rootRoute->def, '/_/1/2');

        self::assertEquals($rootRoute, $actualRoute->parent->parent->parent);
        self::assertEquals($expectedRoute, $actualRoute->parent->parent);
        self::assertEquals($expectedSubRoute, $actualRoute->parent);
        self::assertEquals($expectedSubSubRoute, $actualRoute);
    }
}
