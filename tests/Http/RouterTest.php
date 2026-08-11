<?php

declare(strict_types=1);

namespace LM\WebFramework\Tests\DataStructures;

use GuzzleHttp\Psr7\Request;
use InvalidArgumentException;
use LM\WebFramework\Configuration\Configuration;
use LM\WebFramework\Controller\Exception\RequestedResourceNotFound;
use LM\WebFramework\Http\HttpRequestHandler;
use LM\WebFramework\Http\Model\RouteInfo;
use LM\WebFramework\Http\Router;
use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{
    public function testFindController(): void
    {
        $configData = [
            'rootRoute' => [
                'routes' => [
                    'login' => [
                        'fqcn' => 'LoginController',
                        'allowsAdmins' => false,
                    ],
                    'admin' => [
                        'allowsVisitors' => false,
                        'routes' => [
                            'account' => [
                                'fqcn' => 'AccountController',
                            ],
                            'articles' => [
                                'fqcn' => 'EditArticleController',
                                'allowsVisitors' => false,
                                'args' => [
                                    0 => [],
                                    1 => [],
                                ],
                            ]
                        ]
                    ],
                    'articles' => [
                        'fqcn' => 'ArticlesController',
                        'args' => [
                            0 => [],
                            1 => [
                                'fqcn' => 'ArticleController',
                            ]
                        ]
                    ]
                ]
            ],
        ];

        $router = new Router(new Configuration($configData));

        $this->assertEquals(
            new RouteInfo('LoginController', 0, false, true),
            $router->getRouteInfo(['login']),
        );

        // $this->expectException(RequestedResourceNotFound::class);
        // $router->getRouteInfo(['admin']);

        $this->assertEquals(
            new RouteInfo('EditArticleController', 0, true, false),
            $router->getRouteInfo(['admin', 'articles']),
        );

        $this->assertEquals(
            new RouteInfo('EditArticleController', 1, true, false),
            $router->getRouteInfo(['admin', 'articles', 'mon_article']),
        );

        $this->assertEquals(
            new RouteInfo('ArticlesController', 0, true, true),
            $router->getRouteInfo(['articles']),
        );

        $this->assertEquals(
            new RouteInfo('ArticleController', 1, true, true),
            $router->getRouteInfo(['articles', 'foo']),
        );

        $this->assertEquals(
            new RouteInfo('ArticleController', 1, true, true),
            $router->getRouteInfo(['articles', 'mon-article']),
        );
    }

    public function testGetPathSegments(): void
    {
        $this->assertEquals(
            [],
            HttpRequestHandler::getPathSegments(''),
        );
        
        $this->assertEquals(
            [],
            HttpRequestHandler::getPathSegments('/'),
        );
        
        $this->assertEquals(
            [
                'aui',
            ],
            HttpRequestHandler::getPathSegments('/aui/'),
        );
        
        $this->assertEquals(
            [
                'aui',
                'test',
                'something'
            ],
            HttpRequestHandler::getPathSegments('aui/test/something'),
        );
        
        $this->assertEquals(
            [
                'something',
                'else',
            ],
            HttpRequestHandler::getPathSegments('/something/else?eius&36ab2'),
        );
        
        $this->assertEquals(
            [
                urldecode('a-zA-Z0-9.-_~!$&\'()*+,;=:@'),
            ],
            HttpRequestHandler::getPathSegments('a-zA-Z0-9.-_~!$&\'()*+,;=:@?p=26'),
        );

        $this->expectException(InvalidArgumentException::class);
        HttpRequestHandler::getPathSegments('//');
    }
}
