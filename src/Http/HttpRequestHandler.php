<?php

declare(strict_types=1);

namespace LMWF\Http;

use GuzzleHttp\Psr7\ServerRequest;
use LMWF\Conf\HttpConf;
use LMWF\Http\Controller\Exception\AccessDenied;
use LMWF\Http\Controller\Exception\AlreadyAuthenticated;
use LMWF\Http\Controller\Exception\RequestedResourceNotFound;
use LMWF\ErrorHandling\Log;
use LMWF\Http\Exception\UnsupportedMethodException;
use LMWF\Http\Routing\Exception\RouteNotFoundException;
use LMWF\Http\Routing\Router;
use LMWF\Http\Security\CspNonce;
use LMWF\Session\SessionManager;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

final class HttpRequestHandler
{
    public const SUPPORTED_METHODS = [
        'GET',
        'HEAD',
        'POST',
        'READ',
        'PUT',
        'PATCH',
        'OPTIONS',
        'DELETE',
    ];

    public function __construct(
        private ContainerInterface $container,
        private HttpConf $httpConf,
        private Router $router,
        private SessionManager $session,
        private CspNonce $cspNonce,
    ) {
    }

    /**
     * Generates and send back an HTTP response from PHP globals.
     * */
    public function respondToOngoingRequest(): void
    {
        ServerRequest::fromGlobals()
            |> $this->generateResponse(...)
            |> $this->sendResponse(...);
    }

    /**
     * Generates a response from the given ServerRequestInterface object.
     */
    public function generateResponse(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->httpConf->handleExceptions) {
            Log::info("Exceptions are not handled by the app.");
            return $this->addCspSources($this->generateResponseFromRoute($request));
        }

        Log::info("Exceptions are handled by the app.");
        $serverParamsIfException = [];
        try {
            return $this->addCspSources($this->generateResponseFromRoute($request));
        } catch (Throwable $t) {
            return $this->addCspSources($this->generateResponseFromRouteException($request, $t));
        }
    }

    public function generateResponseFromRoute(ServerRequestInterface $request): ResponseInterface
    {
        if (!in_array($request->getMethod(), self::SUPPORTED_METHODS, true)) {
            throw new UnsupportedMethodException();
        }

        $route = $this->router->getRouteFromPath($this->httpConf->rootRoute, $request->getUri()->getPath());
        Log::info("Request matches controller \"{$route->getFqcn()}\".");
        if (null === $route->getFqcn()) {
            throw new RequestedResourceNotFound();
        }
        $controller = $this->container->get($route->getFqcn());

        // @todo Add real role system
        $roles = $this->session->isUserLoggedIn() ? ['ADMIN'] : ['VISITOR'];

        if (count($route->getRoles()) > 0) {
            Log::info("Route roles are \"" . implode(",", $route->getRoles()) . "\".");
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
        );

        return $response;
    }

    public function generateResponseFromRouteException(
        ServerRequestInterface $request,
        Throwable $t,
    ): ResponseInterface {
        try {
            throw $t;
        } catch (RouteNotFoundException | RequestedResourceNotFound) {
            Log::info("Resource requested by user was not found.");
            $fqcn = $this->httpConf->errorControllers->notFoundFqcn;
        } catch (AlreadyAuthenticated) {
            Log::info("User cannot access this route, already authenticated.");
            $fqcn = $this->httpConf->errorControllers->alreadyLoggedInFqcn;
        } catch (AccessDenied) {
            Log::info("User is not authorized.");
            $fqcn = $this->httpConf->errorControllers->notLoggedInFqcn;
        } catch (UnsupportedMethodException) {
            Log::info("HTTP method is not supported.");
            $fqcn = $this->httpConf->errorControllers->methodNotSupportedFqcn;
        } catch (Throwable) {
            Log::error($t->__toString());
            $fqcn = $this->httpConf->errorControllers->defaultErrorFqcn;
        }

        Log::info("Exception controller FQCN is \"{$fqcn}\".");
        $controller = $this->container->get($fqcn);
        $response = $controller->generateResponse(
            $request,
            [
                'throwable_hash' => hash('sha256', $t->__toString()),
            ],
        );

        return $response;
    }

    private function addCspSources(ResponseInterface $response): ResponseInterface
    {
        if (0 === count($this->httpConf->csp)) {
            return $response;
        }

        $cspHeaderValue = '';
        foreach ($this->httpConf->csp as $directive => $values) {
            if (in_array(HttpConf::NONCE_SPECIFIER, $values, strict: true)) {
                $values = array_map(fn ($value) => HttpConf::NONCE_SPECIFIER === $value ? "'nonce-{$this->cspNonce}'" : $value, $values);
            }
            $cspHeaderValue .= $directive . ' ' . implode(' ', $values) . ';';
        }
        return $response->withAddedHeader('Content-Security-Policy', $cspHeaderValue);
    }

    public function sendResponse(ResponseInterface $response): void
    {
        http_response_code($response->getStatusCode());

        foreach ($response->getHeaders() as $headerName => $headerValues) {
            header($headerName . ': ' . implode(', ', $headerValues));
        };

        echo $response->getBody()->__toString();
    }
}
