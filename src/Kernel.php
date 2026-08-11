<?php

declare(strict_types=1);

namespace LM\WebFramework;

use DI\ContainerBuilder;
use LM\WebFramework\Configuration\Configuration;
use LM\WebFramework\Configuration\HttpConf;
use LM\WebFramework\ErrorHandling\Log;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

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
        $conf = Configuration::createFromEnvFile(
            $projectRootPath,
            $runtimeConfig,
        );

        $cb = new ContainerBuilder();
        if (!$conf->isDev) {
            $cb->enableCompilation("{$conf->appRootPath}/var/cache");
        }
        $container = $cb
            ->addDefinitions([
                Configuration::class => $conf,
                HttpConf::class => $conf->httpConf,
            ])
            ->build()
        ;
        Log::init($logger);

        return $container;
    }

    public static function initWithRuntimeConf(
        array $confData = [],
        array $containerDefinitions = [],
        ?LoggerInterface $logger = null,
    ): ContainerInterface {
        $conf = new Configuration($confData);

        $containerDefinitions += [
                Configuration::class => $conf,
                HttpConf::class => $conf->httpConf,
        ];

        $cb = new ContainerBuilder();
        $container = $cb
            ->addDefinitions($containerDefinitions)
            ->build()
        ;
        Log::init($logger);

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

        return $container;
    }
}
