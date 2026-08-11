<?php

declare(strict_types=1);

namespace LM\WebFramework\Tests\DataStructures;

use InvalidArgumentException;
use LM\WebFramework\Configuration\Configuration;
use LM\WebFramework\Configuration\Exception\SettingNotFoundException;
use LM\WebFramework\DataStructures\AppList;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\DataStructures\Factory\CollectionFactory;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;
use TypeError;
use UnexpectedValueException;

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
                                        'n_args' => 0,
                                    ],
                                ],
                                'account' => [
                                    'controller' => [
                                        'class' => 'AccountController',
                                        'n_args' => 0,
                                    ],
                                ],
                            ]
                        ],
                        'articles' => [
                            'controller' => [
                                'class' => 'ArticleController',
                                'min_n_args' => 1,
                                'max_n_args' => 1,
                            ],
                            'routes' => [
                                'edit' => [
                                    'controller' => [
                                        'class' => 'EditArticleController',
                                        'n_args' => 0,
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
                'class' => 'ArticleController',
                'n_args' => 1,
            ],
            $config->getControllerFqcn(['articles', 'foo']),
        );

        $this->assertEquals(
            [
                'class' => 'ArticleController',
                'n_args' => 1,
            ],
            $config->getControllerFqcn(['articles']),
        );
    }
}
