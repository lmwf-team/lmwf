<?php

declare(strict_types=1);

namespace LMWF\Tests\Http\Routing;

use DomainException;
use LMWF\Http\Routing\Route;
use LMWF\DataStructures\Page;
use LMWF\ErrorHandling\ExceptionCode;
use LMWF\Http\DataStructures\PageConf;
use LMWF\Http\Controller\Issue\RouteNotFoundIssue;
use LMWF\Http\Controller\Issue\RoutingParamIssue;
use LMWF\Http\Controller\Issue\RoutingParamIssueCode;
use LMWF\Http\DataStructures\EntPageConf;
use LMWF\Http\DataStructures\RouteDef;
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
    private Router $router;

    #[\Override]
    public function setUp(): void
    {
        $this->router = new Router();
    }

    public function testEmptyPath(): void
    {
        // sar <3
        $rootDef = new RouteDef(noParamConf: null);

        $expectedRoute = new Route($rootDef, parent: null, seg: '');
        self::assertTrue($expectedRoute->isEqual($this->router->getRouteFromPath($rootDef, '')));
        self::assertTrue($expectedRoute->isEqual($this->router->getRouteFromPath($rootDef, '/')));
    }

    /**
     * An exception should be thrown by the parser if the passed path is not a
     * valid absolute path, or an empty string.
     */
    public function testNonAbsolutePath(): void
    {
        $route = RouteFactory::createRootRoute();

        $this->expectExceptionCode(ExceptionCode::HTTP_ROUTING_ROUTER_GIVEN_PATH_IS_NOT_ABSOLUTE->value);

        $this->router->getRouteFromPath($route->def, 'test');
    }

    public function testNonExistingRoute(): void
    {

        $rootDef = new RouteDef(children: [
            '_' => new RouteDef(noParamConf: null)
        ]);

        self::assertEquals(
            new RouteNotFoundIssue('test'),
            $this->router->getRouteFromPath($rootDef, '/test'),
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

    public function testRouteIdWithSpecialChars(): void
    {
        $subRouteDef = RouteFactory::createDef();
        $rootRoute = RouteFactory::createRootRoute([
            $seg = 'c’est mon idée de route !' => $subRouteDef,
        ]);

        $expectedRoute = new Route($subRouteDef, $rootRoute, $seg);
        self::assertTrue($expectedRoute->isEqual($this->router->getRouteFromPath($rootRoute->def, "/{$seg}")));
    }

    public function testSubRoutes(): void
    {
        $sub1SubRouteDef = RouteFactory::createDef();
        $sub2SubRouteDef = new RouteDef(params: [null, null]);
        $rootRoute = RouteFactory::createRootRoute([
            $seg1 = 'sub1' => $sub1SubRouteDef,
            $seg2 = 'sub2' => $sub2SubRouteDef,
        ]);

        $route1Excepted = new Route($sub1SubRouteDef, $rootRoute, $seg1);
        self::assertTrue($route1Excepted->isEqual($this->router->getRouteFromPath($rootRoute->def, "/$seg1")));

        $route2Params0Expected = new Route($sub2SubRouteDef, $rootRoute, $seg2);
        self::assertTrue($route2Params0Expected->isEqual($this->router->getRouteFromPath($rootRoute->def, "/$seg2")));

        $route2Params1Expected = new Route($sub2SubRouteDef, $route2Params0Expected, $seg2, ['param1']);
        self::assertTrue($route2Params1Expected->isEqual($this->router->getRouteFromPath($rootRoute->def, "/$seg2/param1")));

        $route2Params2Expected = new Route($sub2SubRouteDef, $route2Params1Expected, $seg2, ['param1', 'param2']);
        self::assertTrue($route2Params2Expected->isEqual($this->router->getRouteFromPath($rootRoute->def, "/$seg2/param1/param2")));
    }
}
