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
                        'admin' => [
                            'routes' => [
                                'login' => [
                                    'controller' => [
                                        'class' => 'LoginController',
                                    ],
                                ],
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
            ],
            $config->getControllerFqcn(['admin', 'login']),
        );

        $this->expectException(SettingNotFoundException::class);
        $config->getControllerFqcn(['admin']);

        $this->assertEquals(
            [
                'class' => 'EditArticleController',
                'n_args' => 0,
            ],
            $config->getControllerFqcn(['articles', 'edit']),
        );

        $this->assertEquals(
            [
                'class' => 'ArticlesController',
                'n_args' => 1,
            ],
            $config->getControllerFqcn(['articles', 'foo']),
        );

        $this->assertEquals(
            [
                'class' => 'ArticlesController',
                'n_args' => 1,
            ],
            $config->getControllerFqcn(['articles']),
        );

        $this->assertEquals(
            [
                'class' => 'ArticleController',
                'n_args' => 1,
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
