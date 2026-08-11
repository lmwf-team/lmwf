<?php

declare(strict_types=1);

namespace LMWF\Tests\Conf;

use LMWF\Conf\AppConf;
use LMWF\DataStructures\Factory\CollectionFactory;
use LMWF\Tests\Http\ServerErrorController;
use LMWF\Tests\Mocks\RandomErrorController;
use PHPUnit\Framework\TestCase;

final class ConfTest extends TestCase
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

    public function testCsp(): void
    {
        $csps = [
            'base-uri' => [
                "'none'",
            ],
            'style-src' => [
                "'self'",
            ],
        ];

        $conf = new AppConf(CollectionFactory::createDeepAppObject([
            'csp' => $csps,
        ] + self::VALID_DUMMY_CONF));

        self::assertEquals($csps, $conf->httpConf->csp);
    }

    public function testThumbnailFormats(): void
    {
        $thumbnailFormatsList = [
            [],
            [
                'small' => [
                    'minSizeX' => 256,
                    'minSizeY' => 280,
                    'webpQuality' => 90,
                ],
            ],
            [
                'small' => [
                    'minSizeX' => 256,
                    'minSizeY' => 280,
                    'webpQuality' => 90,
                ],
                'big' => [
                    'minSizeX' => 1920,
                    'minSizeY' => 1080,
                    'webpQuality' => 99,
                ],
            ],
        ];

        foreach ($thumbnailFormatsList as $thumbnailFormats) {
            $confParams = CollectionFactory::createDeepAppObject([
                'thumbnailFormats' => $thumbnailFormats,
            ] + self::VALID_DUMMY_CONF);
            $conf = new AppConf($confParams);

            self::assertEquals($thumbnailFormats, array_map(fn ($imgFormat) => [
                'minSizeX' => $imgFormat->minSizeX,
                'minSizeY' => $imgFormat->minSizeY,
                'webpQuality' => $imgFormat->webpQuality,
            ], $conf->thumbnailFormats));
        }
    }
}
