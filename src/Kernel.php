<?php

declare(strict_types=1);

namespace LM\WebFramework;

use DI\ContainerBuilder;
use Exception;
use GuzzleHttp\Psr7\ServerRequest;
use LM\WebFramework\ErrorHandling\LoggedException;
use LM\WebFramework\ErrorHandling\LoggedThrowable;
use LM\WebFramework\Http\HttpRequestHandler;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

final class Kernel
{
    public const CLI_ID = 'cli';

    public static function initialize(
        string $projectRootPath,
        string $language,
    ): ?ContainerInterface {

        if (self::CLI_ID !== php_sapi_name()) {
            // Basic error handler designed to be fail-proof
            set_error_handler(self::getFailProofErrorHandler());
            set_error_handler(self::getFailProofExceptionHandler());
        }

        $config = new Configuration($projectRootPath, $language);
        
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
                    $throwExceptionRegardless= false;
                    try {
                        if (null !== $config->getLoggerFqcn()) {
                            $container->get($config->getLoggerFqcn())->log($exception);
                        }
                        
                        if ($config->isDev()) {
                            $throwExceptionRegardless = true;
                            throw $exception;
                        } else {
                            try {
                                $response = $container->get(HttpRequestHandler::class)->generateErrorResponse($request, $exception);
                                self::sendResponse($response);
                            } catch (Throwable $t) {
                                $container->get($config->getLoggerFqcn())->log($t);
                                throw $t;
                            }
                        }
                        exit();
                    } catch (Throwable $t) {
                        if ($throwExceptionRegardless) {
                            throw $t;
                        } else {
                            echo("An error just happened.");
                            self::getFailProofErrorHandler()(
                                $t->getCode(),
                                $t->getMessage(),
                                $t->getFile(),
                                $t->getLine(),
                            );
                        }
                    }
                }
            );

            $response = $container->get(HttpRequestHandler::class)->generateResponse($request);

            self::sendResponse($response);

            return null;
        }
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
