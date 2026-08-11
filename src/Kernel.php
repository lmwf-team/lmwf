<?php

declare(strict_types=1);

namespace LM\WebFramework;

use DI\ContainerBuilder;
use LM\WebFramework\Configuration\Configuration;
use LM\WebFramework\DataStructures\Factory\CollectionFactory;
use LM\WebFramework\ErrorHandling\LoggedException;
use LM\WebFramework\ErrorHandling\LogLevel;
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
        $config = Configuration::createFromEnvFile(
            $projectRootPath,
            $language,
            $runtimeConfig,
        );

        if ($config->getLogLevel() === LogLevel::NOTICE) {
            error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
        } else {
            error_reporting(E_ERROR | E_WARNING | E_PARSE);
        }
        
        $cb = new ContainerBuilder();
        if (!$config->isDev()) {
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
}