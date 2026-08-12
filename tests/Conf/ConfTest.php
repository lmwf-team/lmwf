<?php

declare(strict_types=1);

namespace LMWF\Tests\Conf;

use LMWF\Conf\AppConf;
use LMWF\DataStructures\Factory\CollectionFactory;
use LMWF\Tests\Http\ServerErrorController;
use LMWF\Tests\Mocks\ConfMock;
use LMWF\Tests\Mocks\RandomErrorController;
use PHPUnit\Framework\TestCase;

final class ConfTest extends TestCase
{
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

        $conf = ConfMock::createConf([
            'csp' => $csps,
        ]);

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
            $conf = ConfMock::createConf([
                'thumbnailFormats' => $thumbnailFormats,
            ]);

            self::assertEquals($thumbnailFormats, array_map(fn ($imgFormat) => [
                'minSizeX' => $imgFormat->minSizeX,
                'minSizeY' => $imgFormat->minSizeY,
                'webpQuality' => $imgFormat->webpQuality,
            ], $conf->thumbnailFormats));
        }
    }
}
