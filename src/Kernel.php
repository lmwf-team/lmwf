<?php

declare(strict_types=1);

namespace LM\WebFramework;

use DI\ContainerBuilder;
use LM\WebFramework\Conf\AppConf;
use LM\WebFramework\Conf\HttpConf;
use LM\WebFramework\ErrorHandling\Log;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use ErrorException;

/**
 * Initialises the Dependency Injection container, the configuration as well as
 * the logger if the configuration specifies one.
 */
final class Kernel
{
    public const string CLI_ID = 'cli';

    /// @brief Reads the configuration and initialises the container.
    ///
    /// @todo Rename projectRootPath to appRootPath or appFolderPath
    /// @todo Rename to initFromConfFile?
    public static function initialize(
        string $projectRootPath,
        string $language,
        array $runtimeConfig = [],
        ?LoggerInterface $logger = null,
    ): ContainerInterface {
        $conf = AppConf::createFromEnvFile(
            $projectRootPath,
            $runtimeConfig,
        );

        $cb = new ContainerBuilder();
        if (!$conf->isDev) {
            $cb->enableCompilation("{$conf->appRootPath}/var/cache");
        }
        $container = $cb
            ->addDefinitions([
                AppConf::class => $conf,
                HttpConf::class => $conf->httpConf,
            ])
            ->build()
        ;
        Log::init($logger);
        self::initErrorHandler();

        return $container;
    }

    public static function initWithRuntimeConf(
        array $confData = [],
        array $containerDefinitions = [],
        ?LoggerInterface $logger = null,
    ): ContainerInterface {
        $conf = new AppConf($confData);

        $containerDefinitions += [
                AppConf::class => $conf,
                HttpConf::class => $conf->httpConf,
        ];

        $cb = new ContainerBuilder();
        $container = $cb
            ->addDefinitions($containerDefinitions)
            ->build()
        ;
        Log::init($logger);
        self::initErrorHandler();

        return $container;
    }

    public static function initBare(
        array $containerDefinitions = [],
        ?LoggerInterface $logger = null,
    ): ContainerInterface {
        $cb = new ContainerBuilder();
        $container = $cb
            ->addDefinitions($containerDefinitions)
            ->build()
        ;
        Log::init($logger);
        self::initErrorHandler();

        return $container;
    }

    private static function initErrorHandler(): void
    {
        set_error_handler(function (
            int $severity,
            string $message,
            string $file,
            int $line
        ): bool {
            throw new ErrorException($message, 0, $severity, $file, $line);
        });
    }
}
