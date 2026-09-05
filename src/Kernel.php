<?php

declare(strict_types=1);

namespace LMWF;

use DI\ContainerBuilder;
use LMWF\Conf\AppConf;
use LMWF\ErrorHandling\Log;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use ErrorException;
use LMWF\DataStructures\Factory\CollectionFactory;
use LMWF\Http\DataStructures\HttpConf;

/**
 * Provides static methods to initialize the Dependency Injection container,
 * initialize and register the app configuration as a service, uses the specifed
 * logger to initialize the Log class, and to set the error handler to turn any
 * error, warning, or notice into an exception.
 */
final class Kernel
{
    public const string CLI_ID = 'cli';

    /**
     * Initialize the LMWF.
     *
     * Initialize the app configuration (from the provided configuration data,
     * from the provided path containing valid configuration files, or a mix of
     * both), initialize the container (for dependency injection) and register
     * the app configuration with it as well as any extra container definitions,
     * initialize the Log class with the provided logger, and register a PHP
     * error handler to turn any error, warning or notice into an exception.
     *
     * @param ?string $confFolderPath The path to the folder containing the
     * app configuration files, null if all the configuration is provided with
     * $confData.
     * @param array<string, mixed> $confData An array of configuration data to initialize the
     * configuration, to complement the configuration read from a file or
     * replace it if no path was specified.
     * @param array<string, object> $containerDefinitions
     * @param ?LoggerInterface $logger A logger to initialize the Log class
     * with.
     */
    public static function init(
        ?string $confFolderPath,
        array $confData = [],
        array $containerDefinitions = [],
        ?LoggerInterface $logger = null,
        bool $setCustomErrorHandler = true,
    ): ContainerInterface {
        $conf = null === $confFolderPath ?
            new AppConf(CollectionFactory::createDeepAppObject($confData)) :
            AppConf::createFromEnvFile(
                $confFolderPath,
                $confData,
            )
        ;

        $cb = new ContainerBuilder();
        if (!$conf->isDev) {
            $cb->enableCompilation("{$conf->appRootPath}/var/cache");
        }
        $containerDefinitions += [
                AppConf::class => $conf,
                HttpConf::class => $conf->httpConf,
        ];
        $container = $cb
            ->addDefinitions($containerDefinitions)
            ->build()
        ;

        Log::init($logger);

        if (true === $setCustomErrorHandler) {
            self::initErrorHandler();
        }

        return $container;
    }

    /**
     * Initialize only the container and only with the provided definitions.
     *
     * The Log class is not initialized nor is the PHP error handler set.
     *
     * @param array<string, object> $containerDefinitions
     */
    public static function initBare(array $containerDefinitions): ContainerInterface
    {
        return new ContainerBuilder()
            ->addDefinitions($containerDefinitions)
            ->build()
        ;
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
