<?php

declare(strict_types=1);

use LM\WebFramework\Configuration\Configuration;
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
        Configuration::createFromEnvFile(__DIR__ . "/resources/no_dist_files");
    }

    #[WithoutErrorHandler]
    public function testNoFiles(): void
    {
        $this->expectException(ErrorException::class);
        Configuration::createFromEnvFile(__DIR__ . "/resources/no_files");
    }

    #[WithoutErrorHandler]
    public function testEmptyConf(): void
    {
        $this->expectException(ErrorException::class);
        Configuration::createFromEnvFile(__DIR__ . "/resources/empty_conf");
    }

    #[WithoutErrorHandler]
    public function testValidConf(): void
    {
        $jsonDist = json_decode(file_get_contents(__DIR__ . "/resources/valid_conf/lmwf_app.json"), true, flags: JSON_THROW_ON_ERROR);
        $jsonLocal = json_decode(file_get_contents(__DIR__ . "/resources/valid_conf/.lmwf_app.local.json"), true, flags: JSON_THROW_ON_ERROR);
        $conf = Configuration::createFromEnvFile(
            __DIR__ . "/resources/valid_conf",
            [
                'handleExceptions' => true,
                'loggerFqcn' => self::class,
                'routeError404ControllerFQCN' => self::class . '2',
            ],
        );
        $this->assertEquals($conf->uploadRelPath, $jsonDist['uploadRelPath']);
        $this->assertEquals($conf->language, $jsonLocal['language']);
        $this->assertEquals($conf->loggerFqcn, self::class);
        $this->assertEquals($conf->httpConf->routeError404ControllerFQCN, self::class . '2');
        $this->assertEquals($conf->handleExceptions, true);
    }

    public function tearDown(): void
    {
        set_error_handler(null);
        parent::tearDown();
    }
}
