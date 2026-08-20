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
use LMWF\Http\DataStructures\PageMetadata;
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
        $routeDef = new RouteDef(null, null, nArgsLowerLimit: 1, nArgsUpperLimit: 1);

        $this->expectException(DomainException::class);

        $this->router->getRouteFromPath($routeDef, 'test');
    }

    public function testNonExistingRoute(): void
    {

        $rootRoute = RouteFactory::createRootRoute([
            '_' => new RouteDef(null, null, [])
        ]);

        self::assertEquals(
            new RouteNotFoundIssue('test'),
            $this->router->getRouteFromPath($rootRoute->def, '/test'),
        );
    }

    public function testRouteIdWithSpecialChars(): void
    {
        $pageParam = PageParamFactory::create();

        $subrouteId = 'c’est mon idée de route !';
        $subrouteDef = new RouteDef(self::class, $pageParam);

        $rootRoute = RouteFactory::createRootRoute([
            $subrouteId => $subrouteDef,
        ]);
        $subroute = new Route($subrouteDef, $subrouteId, $rootRoute);

        self::assertEquals($subroute, $this->router->getRouteFromPath($rootRoute->def, "/{$subrouteId}"));
    }

    public function testSubroute(): void
    {
        $sub1SubrouteDef = new RouteDef(self::class, PageParamFactory::create());
        $sub2SubrouteDef = new RouteDef(self::class, PageParamFactory::create(), nArgsUpperLimit: 3);

        $rootRoute = RouteFactory::createRootRoute([
            'sub1' => $sub1SubrouteDef,
            'sub2' => $sub2SubrouteDef,
        ]);

        $sub1Route = new Route($sub1SubrouteDef, 'sub1', $rootRoute);
        self::assertEquals($sub1Route, $this->router->getRouteFromPath($rootRoute->def, '/sub1'));

        $sub2Route0Param = new Route($sub2SubrouteDef, 'sub2', $rootRoute);
        self::assertEquals($sub2Route0Param, $this->router->getRouteFromPath($rootRoute->def, '/sub2'));

        $sub2Route1Param = new Route($sub2SubrouteDef, 'sub2', $sub2Route0Param, ['param1']);
        $sub2Route2Params = new Route($sub2SubrouteDef, 'sub2', $sub2Route1Param, ['param1', 'param2']);
        self::assertEquals($sub2Route2Params, $this->router->getRouteFromPath($rootRoute->def, '/sub2/param1/param2'));
    }

    public function testWithEntConf(): void
    {
        $segName = 'users';

        $rootRoute = RouteFactory::createRootRoute([
            $segName => new RouteDef(
                OkController::class,
                new PageConf(
                    'Users',
                    self::BASE_URL,
                    entConf: new PageMetadata(
                        'User: {{ name }}',
                        UserRepo::class,
                    ),
                ),
                nArgsUpperLimit: 1,
            ),
        ]);

        self::assertEquals(
            null,
            $this->pageFactory->getPage($this->router->getRouteFromPath($rootRoute->def, '/')),
        );

        self::assertEquals(
            null,
            $this->pageFactory->getPage($this->router->getRouteFromPath($rootRoute->def, '')),
        );

        $pageWithNoParams = new Page(null, 'Users', url: self::BASE_URL . "/$segName");
        self::assertEquals(
            $pageWithNoParams,
            $this->pageFactory->getPage($this->router->getRouteFromPath($rootRoute->def, "/$segName")),
        );

        self::assertEquals(
            new Page($pageWithNoParams, 'User: ' . UserRepo::USER_NAME, self::BASE_URL . "/$segName/" . UserRepo::USER_ID),
            $this->pageFactory->getPage($this->router->getRouteFromPath($rootRoute->def, "/$segName/" . UserRepo::USER_ID)),
        );

        self::assertEquals(
            new PageEntTitleErr(FormatErr::EntNotFound),
            $this->pageFactory->getPage($this->router->getRouteFromPath($rootRoute->def, "/$segName/typo-" . UserRepo::USER_ID)),
        );
    }

    public function testWithMultipleParams(): void
    {
        $routeDef = new RouteDef(null, null, nArgsUpperLimit: 3);
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
