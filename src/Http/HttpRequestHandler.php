<?php

declare(strict_types=1);

namespace LM\WebFramework\Http;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use InvalidArgumentException;
use LM\WebFramework\Configuration\HttpConf;
use LM\WebFramework\Controller\Exception\AccessDenied;
use LM\WebFramework\Controller\Exception\AlreadyAuthenticated;
use LM\WebFramework\Controller\Exception\RequestedResourceNotFound;
use LM\WebFramework\ErrorHandling\Logger;
use LM\WebFramework\Http\Exception\UnsupportedMethodException;
use LM\WebFramework\Http\Routing\Exception\RouteNotFoundException;
use LM\WebFramework\Http\Routing\RouteDef;
use LM\WebFramework\Http\Routing\Router;
use LM\WebFramework\Session\SessionManager;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

final class HttpRequestHandler
{
    public const SUPPORTED_METHODS = ['GET', 'HEAD', 'POST', 'READ', 'PUT', 'PATCH', 'OPTIONS', 'DELETE'];
    public const UNEXISTING_ROUTE = 1000;

    private RouteDef $rootRoute;

    public function __construct(
        private HttpConf $conf,
        private ContainerInterface $container,
        private SessionManager $session,
    ) {
        $this->rootRoute = $conf->rootRoute;
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
        $segs = Router::getSegmentsFromPath($path);
        Logger::notice("Found segments are \"" . implode(",", $segs) . "\".");
        $params = [];

        if (!$this->conf->handleExceptions) {
            Logger::notice("Exceptions are not handled by the app.");
            return $this->generateResponseFromRoute($request, $segs);
        }

        try {
            Logger::notice("Exceptions are handled by the app.");
            return $this->generateResponseFromRoute($request, $segs);
        } catch (RouteNotFoundException | RequestedResourceNotFound) {
            Logger::notice("Resource requested by user was not found.");
            $fqcn = $this->conf->routeError404ControllerFQCN;
        } catch (AlreadyAuthenticated) {
            Logger::notice("User cannot access this route, already authenticated.");
            $fqcn = $this->conf->routeErrorAlreadyLoggedInControllerFQCN;
        } catch (AccessDenied) {
            Logger::notice("User is not authorized.");
            $fqcn = $this->conf->routeErrorNotLoggedInControllerFQCN;
        } catch (UnsupportedMethodException) {
            Logger::notice("HTTP method is not supported.");
            $fqcn = $this->conf->routeErrorMethodNotSupportedFQCN;
        } catch (Throwable $t) {
            $fqcn = $this->conf->serverErrorControllerFQCN;
            $params = [
                'throwable_hash' => hash('sha256', $t->__toString()),
            ];
        }

        Logger::notice("Actual controller FQCN is \"{$fqcn}\".");
        $controller = $this->container->get($fqcn);
        $response = $controller->generateResponse($request, $segs, $params);

        return $this->addCspSources($response);
    }

    /**
     * @param string[] $segs A list of URL-decoded path segments.
    */
    public function generateResponseFromRoute(ServerRequestInterface $request, array $segs): ResponseInterface
    {
        if (!in_array($request->getMethod(), self::SUPPORTED_METHODS, true)) {
            throw new UnsupportedMethodException();
        }
        $route = (new Router())->getRouteFromSegs($this->rootRoute, null, $segs);
        Logger::notice("Request matches controller \"{$route->getFqcn()}\".");
        $controller = $this->container->get($route->getFqcn());

        // @todo Add real role system
        $roles = $this->session->isUserLoggedIn() ? ['ADMIN'] : ['VISITOR'];

        if (count($route->getRoles()) > 0) {
            Logger::notice("Route roles are \"" . implode(",", $route->getRoles()) . "\".");
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

        $response = $controller->generateResponse(
            $route,
            $request,
            0 === $route->nArgs ? [] : array_slice($segs, -$route->nArgs),
            [],
        );

        return $this->addCspSources($response);
    }

    private function addCspSources(ResponseInterface $response): ResponseInterface
    {
        $cspValues = [];
        if (null !== $this->conf->cspDefaultSources) {
            $cspValues[] = "default-src {$this->conf->cspDefaultSources}";
        }
        if (null !== $this->conf->cspFontSources) {
            $cspValues[] = "font-src {$this->conf->cspFontSources}";
        }
        if (null !== $this->conf->cspObjectSources) {
            $cspValues[] = "object-src {$this->conf->cspObjectSources}";
        }
        if (null !== $this->conf->cspStyleSources) {
            $cspValues[] = "style-src {$this->conf->cspStyleSources}";
        }
        return $response->withAddedHeader('Content-Security-Policy', implode(';', $cspValues));
    }
}
