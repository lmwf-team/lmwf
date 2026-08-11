<?php

declare(strict_types=1);

namespace LM\WebFramework;

use DI\ContainerBuilder;
use LM\WebFramework\Configuration\Configuration;
use LM\WebFramework\DataStructures\Factory\CollectionFactory;
use LM\WebFramework\ErrorHandling\LoggedException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

final class Kernel
{
    public const string CLI_ID = 'cli';

    /// @brief Reads the configuration and initialises the container.
    ///
    /// @todo Rename config to conf or cfg
    /// @todo Rename projectRootPath to appRootPath or appFolderPath
    /// @todo Rename to initFromConfFile?
    public static function initialize(
        string $projectRootPath,
        string $language,
        array $runtimeConfig = [],
    ): ContainerInterface {
        if (self::CLI_ID !== php_sapi_name()) {
            // Basic error handler designed to be fail-proof
            set_error_handler(self::getFailProofErrorHandler());
            set_error_handler(self::getFailProofExceptionHandler());
        }

        $config = Configuration::createFromEnvFile(
            $projectRootPath,
            $language,
            $runtimeConfig,
        );
        
        $cb = new ContainerBuilder();
        if (!$config->isDev()) {
            // @todo Put folder in config
            $cb->enableCompilation("{$config->getPathOfAppDirectory()}/var/cache");
        }
        $container = $cb
            ->addDefinitions([
                Configuration::class => $config,
            ])
            ->build()
        ;

        return $container;
    }

    public static function initWithRuntimeConf(
        array $confData = [],
        array $containerDefinitions = [],
    ): ContainerInterface {
        $conf = new Configuration($confData);

        $containerDefinitions += [
                Configuration::class => $conf,
        ];

        $cb = new ContainerBuilder();
        $container = $cb
            ->addDefinitions($containerDefinitions)
            ->build()
        ;

        return $container;
    }

    public static function getFailProofErrorHandler(): callable
    {
        return function(
            $errNo,
            $errStr,
            $errFile,
            $errLine,
        ): void {
            echo(":(");
            exit();
        };
    }

    public static function getFailProofExceptionHandler(): callable
    {
        return function($exception): void {
            echo(":-(");
            exit();
        };
    }
}
