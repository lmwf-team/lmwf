<?php

declare(strict_types=1);

namespace LM\WebFramework\Tests\DataStructures;

use LM\WebFramework\Configuration\Configuration;
use LM\WebFramework\Configuration\Exception\SettingNotFoundException;
use LM\WebFramework\Controller\Exception\RequestedResourceNotFound;
use LM\WebFramework\DataStructures\Factory\CollectionFactory;
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
            (new CollectionFactory())->createDeepAppObject($configData),
            '.',
            'EN',
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
            [],
            HttpRequestHandler::getPathSegments('//'),
        );
        
        $this->assertEquals(
            [
                'aui',
            ],
            HttpRequestHandler::getPathSegments('/aui/'),
        );
    }
}
