<?php

declare(strict_types=1);

namespace LM\WebFramework\Http;

use LM\WebFramework\Configuration\Configuration;
use LM\WebFramework\Configuration\Exception\SettingNotFoundException;
use LM\WebFramework\Controller\Exception\AccessDenied;
use LM\WebFramework\Controller\Exception\AlreadyAuthenticated;
use LM\WebFramework\Controller\Exception\RequestedResourceNotFound;
use LM\WebFramework\Controller\Exception\RequestedRouteNotFound;
use LM\WebFramework\Session\SessionManager;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

final class HttpRequestHandler
{
    const UNEXISTING_ROUTE = 1000;

    public function __construct(
        private Configuration $configuration,
        private ContainerInterface $container,
        private Router $router,
        private SessionManager $session,
    ) {
    }
    
    public function sendResponse(ResponseInterface $response): void
    {
        http_response_code($response->getStatusCode());

        foreach ($response->getHeaders() as $headerName => $headerValues) {
            header($headerName . ': ' . implode(', ', $headerValues));
        };

        echo $response->getBody()->__toString();
    }

    public function respondToOngoingRequest(): void
    {
        $request = ServerRequest::fromGlobals();
        $this->generateResponse($request);
        $this->sendResponse($response);
    }

    /**
     * Handles the entire process of responding to an HTTP request and return an
     * HTTP response.
     */
    public function generateResponse(ServerRequestInterface $request): ResponseInterface
    {
        session_start();

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

        $pathSegments = $this->getPathSegments($request->getRequestTarget());

        /// @todo Magic strings
        $route = $this->router->getControllerFqcn(
            $pathSegments,
            $this->session->isUserLoggedIn() ? 'admins' : 'visitors',
        );

        /// @todo Create RouteInfo class.
        $controller = $this->container->get($route['class']);

        $response = $this->addCspSources($controller->generateResponse(
            $request,
            0 === $route['n_args'] ? [] : array_slice($pathSegments, -$route['n_args']),
            [],
        ));

        return $repsonse;
    }

    /**
     * @todo Create special interface for Error Response Generators
     */
    public function generateErrorResponse(RequestInterface $request, Throwable $t): ResponseInterface
    {
        $pathSegments = self::getPathSegments($request->getRequestTarget());
        
        if ($t instanceof RequestedRouteNotFound || $t instanceof RequestedResourceNotFound) {
            $response = $this->container->get($this->configuration->getErrorNotFoundControllerFQCN())
                ->generateResponse($request, $pathSegments, [])
            ;
        } elseif ($t instanceof AlreadyAuthenticated) {
            $response = $this->container->get($this->configuration->getErrorLoggedInControllerFQCN())
                ->generateResponse($request, $pathSegments, [])
            ;
        } elseif ($t instanceof AccessDenied) {
            $response = $this->container->get($this->configuration->getErrorNotLoggedInControllerFQCN())
                ->generateResponse($request, $pathSegments, [])
            ;
        } else {
            $response = $this->container->get($this->configuration->getServerErrorControllerFQCN())
                ->generateResponse(
                    $request,
                    $pathSegments,
                    [
                        'throwable_hash' => hash('sha256', $t->__toString()),
                    ],
                )
            ;
        }

        return $this->addCspSources($response);
    }

    /**
     * A Path Segment is defined as any part of the Request Target
     * (origin-form of the composed URI) that is between two slashes,
     * or the last part after the last slash.
     * 
     * @todo Make not static? It would be more OOP.
     * 
     * @return array<string>
     */
    public static function getPathSegments(string $requestTarget): array
    {
        if ('/' === substr($requestTarget, 0, 1)) {
            $requestTarget = substr($requestTarget, 1);
        }
        if ('/' === substr($requestTarget, -1, 1)) {
            $requestTarget = substr($requestTarget, 0, -1);
        }
        $parts = array_map(fn ($e) => urldecode($e), explode('/', $requestTarget));
        if ([''] === $parts) {
            return [];
        } else {
            return $parts;
        }
    }

    private function addCspSources(ResponseInterface $response): ResponseInterface
    {
         // @todo Don’t specify CSP sources if they are not set in configuration
         $cspValues = [
            "default-src {$this->configuration->getCSPDefaultSources()}",
            "font-src {$this->configuration->getCSPFontSources()}",
            "object-src {$this->configuration->getCSPObjectSources()}",
            "style-src {$this->configuration->getCSPStyleSources()}",
        ];
        return $response->withAddedHeader('Content-Security-Policy', implode(';', $cspValues));
    }
}
