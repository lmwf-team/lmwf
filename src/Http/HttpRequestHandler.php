<?php

declare(strict_types=1);

namespace LM\WebFramework\Http;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use InvalidArgumentException;
use LM\WebFramework\Configuration\Configuration;
use LM\WebFramework\Controller\Exception\AccessDenied;
use LM\WebFramework\Controller\Exception\AlreadyAuthenticated;
use LM\WebFramework\Controller\Exception\RequestedResourceNotFound;
use LM\WebFramework\Controller\Exception\RequestedRouteNotFound;
use LM\WebFramework\Http\Error\RoutingError;
use LM\WebFramework\Http\Exception\UnsupportedMethodException;
use LM\WebFramework\Http\Routing\Exception\RouteNotFoundException;
use LM\WebFramework\Http\Routing\ParameterizedRoute;
use LM\WebFramework\Http\Routing\ParentRoute;
use LM\WebFramework\Http\Routing\Route;
use LM\WebFramework\Http\Routing\RouteDefParser;
use LM\WebFramework\Http\Routing\Router;
use LM\WebFramework\Session\SessionManager;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

final class HttpRequestHandler
{
    public const SUPPORTED_METHODS = ['GET', 'HEAD', 'POST', 'READ', 'PUT', 'PATCH', 'OPTIONS', 'DELETE'];
    public const UNEXISTING_ROUTE = 1000;

    private ParameterizedRoute|ParentRoute $rootRoute;

    public function __construct(
        private Configuration $conf,
        private ContainerInterface $container,
        private SessionManager $session,
        RouteDefParser $routeParser,
    ) {
        $this->rootRoute = $routeParser->parse($conf->getRoutes()->toArray(), allowOverridingParentRoles: true);
    }

    public function sendResponse(ResponseInterface $response): void
    {
        http_response_code($response->getStatusCode());

        foreach ($response->getHeaders() as $headerName => $headerValues) {
            header($headerName . ': ' . implode(', ', $headerValues));
        };

        echo $response->getBody()->__toString();
    }

    /// @todo Use pipe operator!
    public function respondToOngoingRequest(): void
    {
        $request = ServerRequest::fromGlobals();

        // set_error_handler(
        //     function ($errNo, $errStr, $errFile, $errLine)
        //     {
        //         $exception = new LoggedException(
        //             $errStr,
        //             $errNo,
        //             $errFile,
        //             $errLine,
        //             time(),
        //         );
        //         throw $exception;
        //     }
        // );

        // set_exception_handler(
        //     function (Throwable $exception) use ($config, $container, $request)
        //     {
        //         if (null !== $config->getLoggerFqcn()) {
        //             $container->get($config->getLoggerFqcn())->info($exception->getMessage());
        //         }
                
        //         if ($config->isDev()) {
        //             throw $exception;
        //         } else {
        //             try {
        //                 $response = $container->get(HttpRequestHandler::class)->generateErrorResponse($request, $exception);
        //                 self::sendResponse($response);
        //             } catch (Throwable $t) {
        //                 $container->get($config->getLoggerFqcn())->info($t->getMessage());
        //                 throw $t;
        //             }
        //         }
        //         exit();
        //     }
        // );
        $response = $this->generateResponse($request);
        $this->sendResponse($response);
    }

    /**
     * Handles the entire process of responding to an HTTP request and return an
     * HTTP response.
     */
    public function generateResponse(ServerRequestInterface $request): ResponseInterface
    {
        $path = $request->getUri()->getPath();
        $segs = $this->getPathSegments($path);
        $params = [];
    
        try {
            return $this->generateResponseFromRoute($request);
        } catch (RouteNotFoundException|RequestedResourceNotFound) {
            $fqcn = $this->conf->getErrorNotFoundControllerFQCN();
        } catch (AlreadyAuthenticated) {
            $fqcn = $this->conf->getErrorLoggedInControllerFQCN();
        } catch (AccessDenied) {
            $fqcn = $this->conf->getErrorNotLoggedInControllerFQCN();
        } catch (UnsupportedMethodException) {
            $fqcn = $this->conf->getErrorMethodNotSupportedFQCN();
        } catch (Throwable $t) {
            $fqcn = $this->conf->getServerErrorControllerFQCN();
            $params = [
                'throwable_hash' => hash('sha256', $t->__toString()),
            ];
        }

        $controller = $this->container->get($fqcn);
        $response = $controller->generateResponse($request, $segs, $params);

        return $this->addCspSources($response);
    }

    public function generateResponseFromRoute(ServerRequestInterface $request): ResponseInterface
    {
        $path = $request->getUri()->getPath();
        $segs = $this->getPathSegments($path);

        if (!in_array($request->getMethod(), self::SUPPORTED_METHODS, true)) {
            throw new UnsupportedMethodException();
        }
        $route = (new Router())->getRouteFromPath($this->rootRoute, $path);
        $controller = $this->container->get($route->getFqcn());

        // @todo Add real role system
        $roles = $this->session->isUserLoggedIn() ? ['ADMIN'] : ['VISITOR'];

        if (count($route->getRoles()) > 0) {
            $isAllowed = false;
            foreach ($roles as $role) {
                if (in_array($role, $route->getRoles(), strict: true)) {
                    $isAllowed = true;
                    break;
                }
            }
            if (!$isAllowed) {
                throw new AccessDenied("User is not allowed.");
            }
        }

        if ($route instanceof Route) {
            $response = $controller->generateResponse(
                $request,
                0 === $route->nArgs ? [] : array_slice($segs, -$route->nArgs),
                [],
            );
        } else {
            $response = $controller->generateResponse($request, [], []);
        }

        return $this->addCspSources($response);
    }

    /**
     * A Path Segment is defined as any part of the Request Target
     * (origin-form of the composed URI) that is between two slashes,
     * or the last part after the last slash.
     * 
     * @todo Make not static? It would be more OOP.
     * @todo Use AppList instead?
     * @todo Make sur the url conform to rfc3986?
     * 
     * @return array<string>
     */
    public static function getPathSegments(string $url): array
    {
        $parsed = parse_url($url, PHP_URL_PATH);
        if (false === $parsed) {
            throw new InvalidArgumentException("Could not parse the given URL: {$url}");
        }
        if ('/' === substr($parsed, 0, 1)) {
            $parsed = substr($parsed, 1);
        }
        if ('/' === substr($parsed, -1, 1)) {
            $parsed = substr($parsed, 0, -1);
        }
        $parts = array_map(fn ($e) => urldecode($e), explode('/', $parsed));
        if ([''] === $parts) {
            return [];
        } else {
            return $parts;
        }
    }

    private function addCspSources(ResponseInterface $response): ResponseInterface
    {
        $cspValues = [];
        if ($this->conf->hasSetting('cspDefaultSources')) {
            $cspValues[] = "default-src {$this->conf->getCSPDefaultSources()}";
        }
        if ($this->conf->hasSetting('cspFontSources')) {
            $cspValues[] = "font-src {$this->conf->getCSPFontSources()}";
        }
        if ($this->conf->hasSetting('cspObjectSources')) {
            $cspValues[] = "object-src {$this->conf->getCSPObjectSources()}";
        }
        if ($this->conf->hasSetting('cspStyleSources')) {
            $cspValues[] = "style-src {$this->conf->getCSPStyleSources()}";
        }
        return $response->withAddedHeader('Content-Security-Policy', implode(';', $cspValues));
    }
}
