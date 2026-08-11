<?php

declare(strict_types=1);

namespace LM\WebFramework;

use DI\ContainerBuilder;
use GuzzleHttp\Psr7\ServerRequest;
use LM\WebFramework\Configuration\Configuration;
use LM\WebFramework\DataStructures\Factory\CollectionFactory;
use LM\WebFramework\ErrorHandling\LoggedException;
use LM\WebFramework\Http\HttpRequestHandler;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

final class Kernel
{
    public const CLI_ID = 'cli';

    public static function initialize(
        string $projectRootPath,
        string $language,
        array $runtimeConfig = [],
    ): ?ContainerInterface {

        if (self::CLI_ID !== php_sapi_name()) {
            // Basic error handler designed to be fail-proof
            set_error_handler(self::getFailProofErrorHandler());
            set_error_handler(self::getFailProofExceptionHandler());
        }

        $config = self::createConfiguration(
            $projectRootPath,
            $language,
            $runtimeConfig,
        );
        
        $cb = (new ContainerBuilder());
        if (!$config->isDev()) {
            // @todo Put folder in config
            $cb->enableCompilation("$projectRootPath/var/cache");
        }
        $container = $cb
            ->addDefinitions([
                Configuration::class => $config,
            ])
            ->build()
        ;

        if (self::CLI_ID === php_sapi_name()) {
            return $container;
        } else {
            session_start();

            $request = ServerRequest::fromGlobals();

            set_error_handler(
                function ($errNo, $errStr, $errFile, $errLine)
                {
                    $exception = new LoggedException(
                        $errStr,
                        $errNo,
                        $errFile,
                        $errLine,
                        time(),
                    );
                    throw $exception;
                }
            );

            set_exception_handler(
                function (Throwable $exception) use ($config, $container, $request)
                {
                    if (null !== $config->getLoggerFqcn()) {
                        $container->get($config->getLoggerFqcn())->info($exception->getMessage());
                    }
                    
                    if ($config->isDev()) {
                        throw $exception;
                    } else {
                        try {
                            $response = $container->get(HttpRequestHandler::class)->generateErrorResponse($request, $exception);
                            self::sendResponse($response);
                        } catch (Throwable $t) {
                            $container->get($config->getLoggerFqcn())->info($t->getMessage());
                            throw $t;
                        }
                    }
                    exit();
                }
            );

            $response = $container->get(HttpRequestHandler::class)->generateResponse($request);

            self::sendResponse($response);

            return null;
        }
    }

    /**
     * @todo Add JSON_THROW_ON_ERROR everywhere, and automatically check its presence.
     */
    public static function createConfiguration(
        string $configFolderPath,
        string $language,
        array $configData = [],
    ): Configuration {
        $env = file_get_contents("$configFolderPath/.env.json");
        $envLocal = file_get_contents("$configFolderPath/.env.json.local");
        $configData += false !== $envLocal ? json_decode($envLocal, true, flags: JSON_THROW_ON_ERROR) : [];
        $configData += false !== $env ? json_decode($env, true, flags: JSON_THROW_ON_ERROR) : [];
        $configData = (new CollectionFactory())->createDeepAppObject($configData);

        return new Configuration(
            $configData,
            $configFolderPath,
            $language,
        );
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
    
    public static function sendResponse(ResponseInterface $response): void
    {
        http_response_code($response->getStatusCode());

        foreach ($response->getHeaders() as $headerName => $headerValues) {
            header($headerName . ': ' . implode(', ', $headerValues));
        };

        echo $response->getBody()->__toString();
    }
}
