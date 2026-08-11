<?php

declare(strict_types=1);

namespace LM\WebFramework\Tests\Http\Routing;

use LM\WebFramework\Http\Routing\Exception\RouteNotFoundException;
use LM\WebFramework\Http\Routing\OnlyChildParentRouteDef;
use LM\WebFramework\Http\Routing\Route;
use LM\WebFramework\Http\Routing\ParameterizedRoute;
use LM\WebFramework\Http\Routing\ParentRoute;
use LM\WebFramework\Http\Routing\Router;
use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{
    public function testHomeUrl(): void
    {
        $routeDef = new ParentRoute(self::class);
        $route = new Route($routeDef, ['']);
        $router = new Router();
        $this->assertEquals($route, $router->getRouteFromPath($routeDef, ''));
        $this->assertEquals($route, $router->getRouteFromPath($routeDef, '/'));
        // $this->assertEquals($route, $router->getRouteFromPath($routeDef, '//'));
        // $this->assertEquals($route, $router->getRouteFromPath($routeDef, '//'));
    }

    public function testRouteWithOnlyChild(): void
    {
        $childRoute = new ParameterizedRoute("App\Controller\SubRouteController", minArgs: 1, maxArgs: 1);
        $routeDef = new OnlyChildParentRouteDef(
            "App\Controller\RouteController",
            $childRoute,
        );
        $router = new Router();
        $this->assertNotNull($router->getRouteFromPath($routeDef, '/argument/'));
        $this->assertNotNull($router->getRouteFromPath($routeDef, '/argument'));
    }

    public function testRouteIdWithSpecialChars(): void
    {
        $subRouteId = 'c’est mon idée de route !';
        $subRouteDef = new ParentRoute(self::class);
        $routeDef = new ParentRoute(self::class, routes: [
            $subRouteId => $subRouteDef,
        ]);
        $route = new Route($routeDef, ['']);
        $subRoute = new Route($subRouteDef, [$subRouteId], $route);
        $router = new Router();
        $this->assertEquals($subRoute, $router->getRouteFromPath($routeDef, "/{$subRouteId}"));
    }

    public function testParameterizedRoute(): void
    {
        $routeDef = new ParameterizedRoute(self::class, minArgs: 1, maxArgs: 1);
        $expected = new Route($routeDef, ['test'], nArgs: 1);
        $router = new Router();
        // $this->assertEquals($expected, $router->getRouteFromPath($routeDef, 'test'));
        $this->assertEquals($expected, $router->getRouteFromPath($routeDef, '/test'));
        // $this->assertEquals($expected, $router->getRouteFromPath($routeDef, '//test'));
        // $this->assertEquals($expected, $router->getRouteFromPath($routeDef, 'test/'));
        // $this->assertEquals($expected, $router->getRouteFromPath($routeDef, '/test/'));
        // $this->assertEquals($expected, $router->getRouteFromPath($routeDef, '//test/'));
        // $this->assertEquals($expected, $router->getRouteFromPath($routeDef, 'test//'));
        // $this->assertEquals($expected, $router->getRouteFromPath($routeDef, '/test//'));
        // $this->assertEquals($expected, $router->getRouteFromPath($routeDef, '//test//'));
    }

    public function testParameterizedRouteWithBadParams0(): void
    {
        $routeDef = new ParameterizedRoute(self::class, minArgs: 1, maxArgs: 1);
        $router = new Router();

        $this->expectException(RouteNotFoundException::class);
        $router->getRouteFromPath($routeDef, '');
    }

    public function testParameterizedRouteWithBadParams1(): void
    {
        $routeDef = new ParameterizedRoute(self::class, minArgs: 1, maxArgs: 1);
        $router = new Router();

        $this->expectException(RouteNotFoundException::class);
        $router->getRouteFromPath($routeDef, '/');
    }

    public function testParameterizedRouteWithBadParams2(): void
    {
        $routeDef = new ParameterizedRoute(self::class, minArgs: 1, maxArgs: 1);
        $router = new Router();

        $this->assertEquals(new Route($routeDef, [''], nArgs: 1), $router->getRouteFromPath($routeDef, '//'));
        $this->assertEquals(new Route($routeDef, ['test'], nArgs: 1), $router->getRouteFromPath($routeDef, '/test'));
        $this->expectException(RouteNotFoundException::class);
        $router->getRouteFromPath($routeDef, '/');
    }

    public function testParameterizedRouteWithBadParams4(): void
    {
        $routeDef = new ParameterizedRoute(self::class, minArgs: 1, maxArgs: 1);
        $router = new Router();

        $this->expectException(RouteNotFoundException::class);
        $router->getRouteFromPath($routeDef, '/test/prout');
    }

    public function testParameterizedRouteWithBadParams5(): void
    {
        $routeDef = new ParameterizedRoute(self::class, minArgs: 1, maxArgs: 1);
        $router = new Router();

        $this->expectException(RouteNotFoundException::class);
        $router->getRouteFromPath($routeDef, '//test/prout');
    }

    public function testParameterizedRouteWithBadParams6(): void
    {
        $routeDef = new ParameterizedRoute(self::class, minArgs: 1, maxArgs: 1);
        $router = new Router();

        $this->expectException(RouteNotFoundException::class);
        $router->getRouteFromPath($routeDef, '/test/prout/');
    }

    public function testParameterizedRouteWithBadParams7(): void
    {
        $routeDef = new ParameterizedRoute(self::class, minArgs: 1, maxArgs: 1);
        $router = new Router();

        $this->expectException(RouteNotFoundException::class);
        $router->getRouteFromPath($routeDef, '/test/prout//');
    }

    public function testNonExistingRoute(): void
    {
        $routeDef = new ParentRoute(self::class, routes: []);
        $router = new Router();
        $this->expectException(RouteNotFoundException::class);
        $router->getRouteFromPath($routeDef, 'test');
        $router->getRouteFromPath($routeDef, '/test');
    }

    public function testSubRoute(): void
    {
        $sub1SubRouteDef = new ParentRoute(self::class);
        $sub2SubRouteDef = new ParameterizedRoute(self::class, maxArgs: 3);
        $subRouteDef = new ParentRoute(
            self::class,
            routes: [
                'sub1' => $sub1SubRouteDef,
                'sub2' => $sub2SubRouteDef,
            ],
        );
        $routeDef = new ParentRoute(self::class, routes: ['sub' => $subRouteDef]);

        $router = new Router();

        $route = new Route($routeDef, ['']);
        $subRoute = new Route($subRouteDef, ['sub'], $route);
        $sub1SubRoute = new Route($sub1SubRouteDef, ['sub1'], $subRoute);
        $sub2SubRoute = new Route($sub2SubRouteDef, ['param1', 'param2'], $subRoute, nArgs: 2);

        $this->assertEquals($subRoute, $router->getRouteFromPath($routeDef, '/sub'));
        // $this->assertEquals($subRoute, $router->getRouteFromPath($routeDef, '/sub/'));
        // $this->assertEquals($subRoute, $router->getRouteFromPath($routeDef, '/sub//'));
        $this->assertEquals($subRoute, $router->getRouteFromPath($routeDef, '/sub'));
        // $this->assertEquals($subRoute, $router->getRouteFromPath($routeDef, '/sub/'));
        // $this->assertEquals($subRoute, $router->getRouteFromPath($routeDef, '/sub//'));
        // $this->assertEquals($subRoute, $router->getRouteFromPath($routeDef, '//sub'));
        // // $this->assertEquals($subRoute, $router->getRouteFromPath($routeDef, '//sub/'));
        // // // $this->assertEquals($subRoute, $router->getRouteFromPath($routeDef, '//sub//'));
        // $this->assertEquals($sub1SubRoute, $router->getRouteFromPath($routeDef, 'sub/sub1'));
        // $this->assertEquals($sub1SubRoute, $router->getRouteFromPath($routeDef, 'sub/sub1/'));
        // $this->assertEquals($sub1SubRoute, $router->getRouteFromPath($routeDef, 'sub/sub1//'));
        $this->assertEquals($sub1SubRoute, $router->getRouteFromPath($routeDef, '/sub/sub1'));
        // $this->assertEquals($sub1SubRoute, $router->getRouteFromPath($routeDef, '/sub/sub1/'));
        // $this->assertEquals($sub1SubRoute, $router->getRouteFromPath($routeDef, '/sub/sub1//'));
        // // $this->assertEquals($sub1SubRoute, $router->getRouteFromPath($routeDef, '//sub/sub1'));
        // // $this->assertEquals($sub1SubRoute, $router->getRouteFromPath($routeDef, '//sub/sub1/'));
        // // $this->assertEquals($sub1SubRoute, $router->getRouteFromPath($routeDef, '//sub/sub1//'));
        // $this->assertEquals($sub1SubRoute, $router->getRouteFromPath($routeDef, 'sub//sub1'));
        // $this->assertEquals($sub1SubRoute, $router->getRouteFromPath($routeDef, 'sub//sub1/'));
        // $this->assertEquals($sub1SubRoute, $router->getRouteFromPath($routeDef, 'sub//sub1//'));
        // $this->assertEquals($sub1SubRoute, $router->getRouteFromPath($routeDef, '/sub//sub1'));
        // $this->assertEquals($sub1SubRoute, $router->getRouteFromPath($routeDef, '/sub//sub1/'));
        // $this->assertEquals($sub1SubRoute, $router->getRouteFromPath($routeDef, '/sub//sub1//'));
        // // $this->assertEquals($sub1SubRoute, $router->getRouteFromPath($routeDef, '//sub//sub1'));
        // // $this->assertEquals($sub1SubRoute, $router->getRouteFromPath($routeDef, '//sub//sub1/'));
        // // $this->assertEquals($sub1SubRoute, $router->getRouteFromPath($routeDef, '//sub//sub1//'));
        // $this->assertEquals($sub1SubRoute, $router->getRouteFromPath($routeDef, 'sub///sub1'));
        // $this->assertEquals($sub1SubRoute, $router->getRouteFromPath($routeDef, 'sub///sub1/'));
        // $this->assertEquals($sub1SubRoute, $router->getRouteFromPath($routeDef, 'sub///sub1//'));
        // $this->assertEquals($sub1SubRoute, $router->getRouteFromPath($routeDef, '/sub///sub1'));
        // $this->assertEquals($sub1SubRoute, $router->getRouteFromPath($routeDef, '/sub///sub1/'));
        // $this->assertEquals($sub1SubRoute, $router->getRouteFromPath($routeDef, '/sub///sub1//'));
        // // $this->assertEquals($sub1SubRoute, $router->getRouteFromPath($routeDef, '//sub///sub1'));
        // // $this->assertEquals($sub1SubRoute, $router->getRouteFromPath($routeDef, '//sub///sub1/'));
        // // $this->assertEquals($sub1SubRoute, $router->getRouteFromPath($routeDef, '//sub///sub//'));

        // $this->assertEquals($sub2SubRoute, $router->getRouteFromPath($routeDef, 'sub/sub2/param1/param2'));
        // $this->assertEquals($sub2SubRoute, $router->getRouteFromPath($routeDef, 'sub/sub2/param1/param2/'));
        // $this->assertEquals($sub2SubRoute, $router->getRouteFromPath($routeDef, 'sub/sub2/param1/param2//'));
        $this->assertEquals($sub2SubRoute, $router->getRouteFromPath($routeDef, '/sub/sub2/param1/param2'));
        // $this->assertEquals($sub2SubRoute, $router->getRouteFromPath($routeDef, '/sub/sub2/param1/param2/'));
        // $this->assertEquals($sub2SubRoute, $router->getRouteFromPath($routeDef, '/sub/sub2/param1/param2//'));
        // // $this->assertEquals($sub2SubRoute, $router->getRouteFromPath($routeDef, '//sub/sub2/param1/param2'));
        // // $this->assertEquals($sub2SubRoute, $router->getRouteFromPath($routeDef, '//sub/sub2/param1/param2/'));
        // // $this->assertEquals($sub2SubRoute, $router->getRouteFromPath($routeDef, '//sub/sub2/param1/param2//'));
        // $this->assertEquals($sub2SubRoute, $router->getRouteFromPath($routeDef, 'sub//sub2/param1/param2'));
        // $this->assertEquals($sub2SubRoute, $router->getRouteFromPath($routeDef, 'sub//sub2/param1/param2/'));
        // $this->assertEquals($sub2SubRoute, $router->getRouteFromPath($routeDef, 'sub//sub2/param1/param2//'));
        // $this->assertEquals($sub2SubRoute, $router->getRouteFromPath($routeDef, '/sub//sub2/param1/param2'));
        // $this->assertEquals($sub2SubRoute, $router->getRouteFromPath($routeDef, '/sub//sub2/param1/param2/'));
        // $this->assertEquals($sub2SubRoute, $router->getRouteFromPath($routeDef, '/sub//sub2/param1/param2//'));
        // // $this->assertEquals($sub2SubRoute, $router->getRouteFromPath($routeDef, '//sub//sub2/param1/param2'));
        // // $this->assertEquals($sub2SubRoute, $router->getRouteFromPath($routeDef, '//sub//sub2/param1/param2/'));
        // // $this->assertEquals($sub2SubRoute, $router->getRouteFromPath($routeDef, '//sub//sub2/param1/param2//'));
        // $this->assertEquals($sub2SubRoute, $router->getRouteFromPath($routeDef, 'sub///sub2/param1/param2'));
        // $this->assertEquals($sub2SubRoute, $router->getRouteFromPath($routeDef, 'sub///sub2/param1/param2/'));
        // $this->assertEquals($sub2SubRoute, $router->getRouteFromPath($routeDef, 'sub///sub2/param1/param2//'));
        // $this->assertEquals($sub2SubRoute, $router->getRouteFromPath($routeDef, '/sub///sub2/param1/param2'));
        // $this->assertEquals($sub2SubRoute, $router->getRouteFromPath($routeDef, '/sub///sub2/param1/param2/'));
        // $this->assertEquals($sub2SubRoute, $router->getRouteFromPath($routeDef, '/sub///sub2/param1/param2//'));
        // // $this->assertEquals($sub2SubRoute, $router->getRouteFromPath($routeDef, '//sub///sub2/param1/param2'));
        // // $this->assertEquals($sub2SubRoute, $router->getRouteFromPath($routeDef, '//sub///sub2/param1/param2/'));
        // // $this->assertEquals($sub2SubRoute, $router->getRouteFromPath($routeDef, '//sub///sub2/param1/param2//'));
    }

    // public function testFindController(): void
    // {
    //     $configData = [
    //         'rootRoute' => [
    //             'routes' => [
    //                 'login' => [
    //                     'fqcn' => 'LoginController',
    //                     'allowsAdmins' => false,
    //                 ],
    //                 'admin' => [
    //                     'allowsVisitors' => false,
    //                     'routes' => [
    //                         'account' => [
    //                             'fqcn' => 'AccountController',
    //                         ],
    //                         'articles' => [
    //                             'fqcn' => 'EditArticleController',
    //                             'allowsVisitors' => false,
    //                             'args' => [
    //                                 0 => [],
    //                                 1 => [],
    //                             ],
    //                         ]
    //                     ]
    //                 ],
    //                 'articles' => [
    //                     'fqcn' => 'ArticlesController',
    //                     'args' => [
    //                         0 => [],
    //                         1 => [
    //                             'fqcn' => 'ArticleController',
    //                         ]
    //                     ]
    //                 ]
    //             ]
    //         ],
    //     ];

    //     $router = new RoutingRouter(new Configuration($configData));

    //     $this->assertEquals(
    //         new RouteInfo('LoginController', 0, false, true),
    //         $router->getRouteInfo(['login']),
    //     );

    //     // $this->expectException(RequestedResourceNotFound::class);
    //     // $router->getRouteInfo(['admin']);

    //     $this->assertEquals(
    //         new RouteInfo('EditArticleController', 0, true, false),
    //         $router->getRouteInfo(['admin', 'articles']),
    //     );

    //     $this->assertEquals(
    //         new RouteInfo('EditArticleController', 1, true, false),
    //         $router->getRouteInfo(['admin', 'articles', 'mon_article']),
    //     );

    //     $this->assertEquals(
    //         new RouteInfo('ArticlesController', 0, true, true),
    //         $router->getRouteInfo(['articles']),
    //     );

    //     $this->assertEquals(
    //         new RouteInfo('ArticleController', 1, true, true),
    //         $router->getRouteInfo(['articles', 'foo']),
    //     );

    //     $this->assertEquals(
    //         new RouteInfo('ArticleController', 1, true, true),
    //         $router->getRouteInfo(['articles', 'mon-article']),
    //     );
    // }

    // public function testGetPathSegments(): void
    // {
    //     $this->assertEquals(
    //         [],
    //         HttpRequestHandler::getPathSegments(''),
    //     );

    //     $this->assertEquals(
    //         [],
    //         HttpRequestHandler::getPathSegments('/'),
    //     );

    //     $this->assertEquals(
    //         [
    //             'aui',
    //         ],
    //         HttpRequestHandler::getPathSegments('/aui/'),
    //     );

    //     $this->assertEquals(
    //         [
    //             'aui',
    //             'test',
    //             'something'
    //         ],
    //         HttpRequestHandler::getPathSegments('aui/test/something'),
    //     );

    //     $this->assertEquals(
    //         [
    //             'something',
    //             'else',
    //         ],
    //         HttpRequestHandler::getPathSegments('/something/else?eius&36ab2'),
    //     );

    //     $this->assertEquals(
    //         [
    //             urldecode('a-zA-Z0-9.-_~!$&\'()*+,;=:@'),
    //         ],
    //         HttpRequestHandler::getPathSegments('a-zA-Z0-9.-_~!$&\'()*+,;=:@?p=26'),
    //     );

    //     $this->expectException(InvalidArgumentException::class);
    //     HttpRequestHandler::getPathSegments('//');
    // }
}
