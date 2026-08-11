<?php

declare(strict_types=1);

namespace LM\WebFramework\Tests\DataStructures;

use LM\WebFramework\Configuration\Configuration;
use LM\WebFramework\Configuration\Exception\SettingNotFoundException;
use LM\WebFramework\DataStructures\Factory\CollectionFactory;
use LM\WebFramework\Http\HttpRequestHandler;
use PHPUnit\Framework\TestCase;

final class HttpRequestHandlerTest extends TestCase
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

        $this->assertEquals(
            [
                'class' => 'LoginController',
                'n_args' => 0,
                'roles' => [
                    'admins' => false,
                    'visitors' => true,
                ],
            ],
            $config->getControllerFqcn(['login']),
        );

        $this->expectException(SettingNotFoundException::class);
        $config->getControllerFqcn(['admin']);

        $this->assertEquals(
            [
                'class' => 'EditArticleController',
                'n_args' => 0,
                'roles' => [
                    'admins' => true,
                    'visitors' => false,
                ],
            ],
            $config->getControllerFqcn(['articles', 'edit']),
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
            $config->getControllerFqcn(['articles', 'foo']),
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
            $config->getControllerFqcn(['articles']),
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
            $config->getControllerFqcn(['article', 'mon-article']),
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
