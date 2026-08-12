<?php

declare(strict_types=1);

namespace LMWF\Tests\Mocks;

use LMWF\Conf\AppConf;
use LMWF\DataStructures\Factory\CollectionFactory;

final readonly class ConfMock
{
    const array VALID_DUMMY_CONF = [
        'thumbnailFormats' => [],
        'handleExceptions' => true,
        'isDev' => true,
        'homeUrl' => 'http://localhost',
        'language' => 'en',
        'appRootPath' => __DIR__,
        'uploadRelPath' => 'upload',
        'publicRelPath' => 'public',
        'csp' => [],
        'rootRoute' => [
            'roles' => [],
        ],
        'errorControllers' => [
            'alreadyLoggedInFqcn' => RandomErrorController::class,
            'defaultErrorFqcn' => RandomErrorController::class,
            'methodNotSupportedFqcn' => RandomErrorController::class,
            'notFoundFqcn' => RandomErrorController::class,
            'notLoggedInFqcn' => RandomErrorController::class,
        ]

    ];

    public static function createConf(array $confParams): AppConf
    {
        return new AppConf(CollectionFactory::createDeepAppObject($confParams + self::VALID_DUMMY_CONF));
    }
}