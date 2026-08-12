<?php

declare(strict_types=1);

namespace LMWF\Tests\File;

use LMWF\File\FileService;
use LMWF\Tests\Mocks\ConfMock;
use PHPUnit\Framework\TestCase;

final class FileServiceTest extends TestCase
{
    public function testGetUploadedImages(): void
    {
        $fileService = new FileService(ConfMock::createConf([
            'appRootPath' => __DIR__,
            'uploadRelPath' => 'resources',
        ]));
        self::assertEquals([
            'Alternate_Example_2.webp',
        ], $fileService->getUploadedImages());
    }
}
