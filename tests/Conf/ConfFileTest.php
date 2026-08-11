<?php

declare(strict_types=1);

use LM\WebFramework\Conf\AppConf;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;
use PHPUnit\Framework\TestCase;

final class ConfFileTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
        }, E_WARNING);
    }

    #[WithoutErrorHandler]
    public function testNoDistFile(): void
    {
        $this->expectException(ErrorException::class);
        AppConf::createFromEnvFile(__DIR__ . "/resources/no_dist_files");
    }

    #[WithoutErrorHandler]
    public function testNoFiles(): void
    {
        $this->expectException(ErrorException::class);
        AppConf::createFromEnvFile(__DIR__ . "/resources/no_files");
    }

    #[WithoutErrorHandler]
    public function testEmptyConf(): void
    {
        $this->expectException(ErrorException::class);
        AppConf::createFromEnvFile(__DIR__ . "/resources/empty_conf");
    }

    #[WithoutErrorHandler]
    public function testValidConf(): void
    {
        $jsonDist = json_decode(file_get_contents(__DIR__ . "/resources/valid_conf/lmwf_app.json"), true, flags: JSON_THROW_ON_ERROR);
        $jsonLocal = json_decode(file_get_contents(__DIR__ . "/resources/valid_conf/.lmwf_app.local.json"), true, flags: JSON_THROW_ON_ERROR);
        $conf = AppConf::createFromEnvFile(
            __DIR__ . "/resources/valid_conf",
            [
                'handleExceptions' => true,
                'errorControllers' => [
                    'alreadyLoggedInFqcn' => self::class . '2',
                    'defaultErrorFqcn' => self::class . '2',
                    'methodNotSupportedFqcn' => self::class . '2',
                    'notFoundFqcn' => self::class . '2',
                    'notLoggedInFqcn' => self::class . '2',
                    'notFoundFqcn' => self::class . '2',
                ]
            ],
        );
        $this->assertEquals($conf->uploadRelPath, $jsonDist['uploadRelPath']);
        $this->assertEquals($conf->language, $jsonLocal['language']);
        $this->assertEquals($conf->httpConf->errorControllers->notFoundFqcn, self::class . '2');
        $this->assertEquals($conf->handleExceptions, true);
    }

    #[WithoutErrorHandler]
    public function testValidConf2(): void
    {
        $jsonLocal = json_decode(file_get_contents(__DIR__ . "/resources/valid_conf/.lmwf_app.local.json"), true, flags: JSON_THROW_ON_ERROR);
        $conf = AppConf::createFromEnvFile(
            __DIR__ . "/resources/valid_conf",
            [
                'handleExceptions' => true,
            ]
        );
        $this->assertEquals($conf->httpConf->errorControllers->notFoundFqcn, 'Controllers\NotFoundController');
    }

    public function tearDown(): void
    {
        set_error_handler(null);
        parent::tearDown();
    }
}
