<?php

declare(strict_types=1);

namespace LM\WebFramework\Tests\Http\Routing;

use InvalidArgumentException;
use LM\WebFramework\Configuration\Configuration;
use LM\WebFramework\Http\HttpRequestHandler;
use LM\WebFramework\Http\Model\RouteInfo;
use LM\WebFramework\Http\Routing\Exception\RouteNotFoundException;
use LM\WebFramework\Http\Routing\Route;
use LM\WebFramework\Http\Routing\ParameterizedRoute;
use LM\WebFramework\Http\Routing\ParentRoute;
use LM\WebFramework\Http\Routing\RouteDef;
use LM\WebFramework\Http\Routing\Router;
use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{
    public function testHomeUrl(): void
    {
        $routeDef = new ParentRoute(self::class);
        $route = new Route($routeDef);
        $router = new Router($routeDef);
        $this->assertEquals($route, $router->getRouteFromPath(''));
        $this->assertEquals($route, $router->getRouteFromPath('/'));
        $this->assertEquals($route, $router->getRouteFromPath('//'));
        $this->assertEquals($route, $router->getRouteFromPath('//'));
    }
    
    public function testParameterizedRoute(): void
    {
        $route = new ParameterizedRoute(self::class, minArgs: 1, maxArgs: 1);
        $expected = new Route($route, nArgs: 1);
        $router = new Router($route);
        $this->assertEquals($expected, $router->getRouteFromPath('test'));
        $this->assertEquals($expected, $router->getRouteFromPath('/test'));
        $this->assertEquals($expected, $router->getRouteFromPath('//test'));
        $this->assertEquals($expected, $router->getRouteFromPath('test/'));
        $this->assertEquals($expected, $router->getRouteFromPath('/test/'));
        $this->assertEquals($expected, $router->getRouteFromPath('//test/'));
        $this->assertEquals($expected, $router->getRouteFromPath('test//'));
        $this->assertEquals($expected, $router->getRouteFromPath('/test//'));
        $this->assertEquals($expected, $router->getRouteFromPath('//test//'));
    }
    
    public function testParameterizedRouteWithBadParams0(): void
    {
        $route = new ParameterizedRoute(self::class, minArgs: 1, maxArgs: 1);
        $router = new Router($route);

        $this->expectException(RouteNotFoundException::class);
        $router->getRouteFromPath('');
    }
    
    public function testParameterizedRouteWithBadParams1(): void
    {
        $route = new ParameterizedRoute(self::class, minArgs: 1, maxArgs: 1);
        $router = new Router($route);

        $this->expectException(RouteNotFoundException::class);
        $router->getRouteFromPath('/');
    }
    
    public function testParameterizedRouteWithBadParams2(): void
    {
        $route = new ParameterizedRoute(self::class, minArgs: 1, maxArgs: 1);
        $router = new Router($route);

        $this->expectException(RouteNotFoundException::class);
        $router->getRouteFromPath('//');
    }
    
    public function testParameterizedRouteWithBadParams3(): void
    {
        $route = new ParameterizedRoute(self::class, minArgs: 1, maxArgs: 1);
        $router = new Router($route);

        $this->expectException(RouteNotFoundException::class);
        $router->getRouteFromPath('test/prout');
    }
    
    public function testParameterizedRouteWithBadParams4(): void
    {
        $route = new ParameterizedRoute(self::class, minArgs: 1, maxArgs: 1);
        $router = new Router($route);

        $this->expectException(RouteNotFoundException::class);
        $router->getRouteFromPath('/test/prout');
    }
    
    public function testParameterizedRouteWithBadParams5(): void
    {
        $route = new ParameterizedRoute(self::class, minArgs: 1, maxArgs: 1);
        $router = new Router($route);

        $this->expectException(RouteNotFoundException::class);
        $router->getRouteFromPath('//test/prout');
    }
    
    public function testParameterizedRouteWithBadParams6(): void
    {
        $route = new ParameterizedRoute(self::class, minArgs: 1, maxArgs: 1);
        $router = new Router($route);

        $this->expectException(RouteNotFoundException::class);
        $router->getRouteFromPath('/test/prout/');
    }
    
    public function testParameterizedRouteWithBadParams7(): void
    {
        $route = new ParameterizedRoute(self::class, minArgs: 1, maxArgs: 1);
        $router = new Router($route);

        $this->expectException(RouteNotFoundException::class);
        $router->getRouteFromPath('/test/prout//');
    }
    
    public function testNonExistingRoute(): void
    {
        $route = new ParentRoute(self::class, routes: []);
        $router = new Router($route);
        $this->expectException(RouteNotFoundException::class);
        $router->getRouteFromPath('test');
        $router->getRouteFromPath('/test');
    }

    public function testSubRoute(): void
    {
        $subsubRouteDef = new ParentRoute(self::class);
        $subsubRoute = new Route($subsubRouteDef);
        $subRouteDef = new ParentRoute(self::class, routes: ['sub' => $subsubRouteDef]);
        $subRoute = new Route($subRouteDef);
        $route = new ParentRoute(self::class, routes: ['test' => $subRouteDef]);
        $router = new Router($route);
        $this->assertEquals($subRoute, $router->getRouteFromPath('test'));
        $this->assertEquals($subRoute, $router->getRouteFromPath('test/'));
        $this->assertEquals($subRoute, $router->getRouteFromPath('test//'));
        $this->assertEquals($subRoute, $router->getRouteFromPath('/test'));
        $this->assertEquals($subRoute, $router->getRouteFromPath('/test/'));
        $this->assertEquals($subRoute, $router->getRouteFromPath('/test//'));
        $this->assertEquals($subRoute, $router->getRouteFromPath('//test'));
        $this->assertEquals($subRoute, $router->getRouteFromPath('//test/'));
        $this->assertEquals($subRoute, $router->getRouteFromPath('//test//'));
        $this->assertEquals($subsubRoute, $router->getRouteFromPath('test/sub'));
        $this->assertEquals($subsubRoute, $router->getRouteFromPath('test/sub/'));
        $this->assertEquals($subsubRoute, $router->getRouteFromPath('test/sub//'));
        $this->assertEquals($subsubRoute, $router->getRouteFromPath('/test/sub'));
        $this->assertEquals($subsubRoute, $router->getRouteFromPath('/test/sub/'));
        $this->assertEquals($subsubRoute, $router->getRouteFromPath('/test/sub//'));
        $this->assertEquals($subsubRoute, $router->getRouteFromPath('//test/sub'));
        $this->assertEquals($subsubRoute, $router->getRouteFromPath('//test/sub/'));
        $this->assertEquals($subsubRoute, $router->getRouteFromPath('//test/sub//'));
        $this->assertEquals($subsubRoute, $router->getRouteFromPath('test//sub'));
        $this->assertEquals($subsubRoute, $router->getRouteFromPath('test//sub/'));
        $this->assertEquals($subsubRoute, $router->getRouteFromPath('test//sub//'));
        $this->assertEquals($subsubRoute, $router->getRouteFromPath('/test//sub'));
        $this->assertEquals($subsubRoute, $router->getRouteFromPath('/test//sub/'));
        $this->assertEquals($subsubRoute, $router->getRouteFromPath('/test//sub//'));
        $this->assertEquals($subsubRoute, $router->getRouteFromPath('//test//sub'));
        $this->assertEquals($subsubRoute, $router->getRouteFromPath('//test//sub/'));
        $this->assertEquals($subsubRoute, $router->getRouteFromPath('//test//sub//'));
        $this->assertEquals($subsubRoute, $router->getRouteFromPath('test///sub'));
        $this->assertEquals($subsubRoute, $router->getRouteFromPath('test///sub/'));
        $this->assertEquals($subsubRoute, $router->getRouteFromPath('test///sub//'));
        $this->assertEquals($subsubRoute, $router->getRouteFromPath('/test///sub'));
        $this->assertEquals($subsubRoute, $router->getRouteFromPath('/test///sub/'));
        $this->assertEquals($subsubRoute, $router->getRouteFromPath('/test///sub//'));
        $this->assertEquals($subsubRoute, $router->getRouteFromPath('//test///sub'));
        $this->assertEquals($subsubRoute, $router->getRouteFromPath('//test///sub/'));
        $this->assertEquals($subsubRoute, $router->getRouteFromPath('//test///sub//'));
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
