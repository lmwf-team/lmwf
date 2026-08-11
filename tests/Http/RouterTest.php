<?php

declare(strict_types=1);

namespace LM\WebFramework\Tests\DataStructures;

use GuzzleHttp\Psr7\Request;
use InvalidArgumentException;
use LM\WebFramework\Configuration\Configuration;
use LM\WebFramework\Controller\Exception\RequestedResourceNotFound;
use LM\WebFramework\Http\HttpRequestHandler;
use LM\WebFramework\Http\Router;
use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{
    public function testFindController(): void
    {
        $configData =
            [
                'rootRoute' => [
                    'routes' => [
                        'login' => [
                            'roles' => [
                                'admins' => false,
                            ],
                            'controller' => [
                                'class' => 'LoginController',
                            ],
                        ],
                        'admin' => [
                            'roles' => [
                                'visitors' => false,
                            ],
                            'routes' => [
                                'account' => [
                                    'controller' => [
                                        'class' => 'AccountController',
                                    ],
                                ],
                            ]
                        ],
                        'article' => [
                            'controller' => [
                                'class' => 'ArticleController',
                                'n_args' => 1,
                            ]
                        ],
                        'articles' => [
                            'controller' => [
                                'class' => 'ArticlesController',
                                'n_args' => 1,
                            ],
                            'routes' => [
                                'edit' => [
                                    'roles' => [
                                        'visitors' => false,
                                    ],
                                    'controller' => [
                                        'class' => 'EditArticleController',
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
            ]
        ;

        $config = new Configuration(
            $configData,
            // '.',
            // 'EN',
        );

        $router = new Router($config);

        $this->assertEquals(
            [
                'class' => 'LoginController',
                'n_args' => 0,
                'roles' => [
                    'admins' => false,
                    'visitors' => true,
                ],
            ],
            $router->getControllerFqcn(['login']),
        );

        $this->expectException(RequestedResourceNotFound::class);
        $router->getControllerFqcn(['admin']);

        $this->assertEquals(
            [
                'class' => 'EditArticleController',
                'n_args' => 0,
                'roles' => [
                    'admins' => true,
                    'visitors' => false,
                ],
            ],
            $router->getControllerFqcn(['articles', 'edit']),
        );

        $this->assertEquals(
            [
                'class' => 'ArticlesController',
                'n_args' => 1,
                'roles' => [
                    'admins' => true,
                    'visitors' => true,
                ],
            ],
            $router->getControllerFqcn(['articles', 'foo']),
        );

        $this->assertEquals(
            [
                'class' => 'ArticlesController',
                'n_args' => 1,
                'roles' => [
                    'admins' => true,
                    'visitors' => true,
                ],
            ],
            $router->getControllerFqcn(['articles']),
        );

        $this->assertEquals(
            [
                'class' => 'ArticleController',
                'n_args' => 1,
                'roles' => [
                    'admins' => true,
                    'visitors' => true,
                ],
            ],
            $router->getControllerFqcn(['article', 'mon-article']),
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
