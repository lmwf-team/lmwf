<?php

declare(strict_types=1);

namespace LM\WebFramework\Http;

use GuzzleHttp\Psr7\ServerRequest;
use LM\WebFramework\Conf\HttpConf;
use LM\WebFramework\Controller\Exception\AccessDenied;
use LM\WebFramework\Controller\Exception\AlreadyAuthenticated;
use LM\WebFramework\Controller\Exception\RequestedResourceNotFound;
use LM\WebFramework\ErrorHandling\Log;
use LM\WebFramework\Http\Exception\UnsupportedMethodException;
use LM\WebFramework\Http\Routing\Exception\RouteNotFoundException;
use LM\WebFramework\Http\Routing\RouteDef;
use LM\WebFramework\Http\Routing\Router;
use LM\WebFramework\Http\Security\CspNonce;
use LM\WebFramework\Session\SessionManager;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

final class HttpRequestHandler
{
    public const SUPPORTED_METHODS = ['GET', 'HEAD', 'POST', 'READ', 'PUT', 'PATCH', 'OPTIONS', 'DELETE'];
    public const UNEXISTING_ROUTE = 1000;

    public function __construct(
        private ContainerInterface $container,
        private HttpConf $httpConf,
        private Router $router,
        private SessionManager $session,
        private CspNonce $cspNonce,
    ) {
    }

    /**
     * Generates a response from globals.
     *
     * @todo Use pipe operator!
     * */
    public function respondToOngoingRequest(): void
    {
        // @todo Kind of a hack
        $request = ServerRequest::fromGlobals();
        $response = $this->generateResponse($request);
        $this->sendResponse($response);
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
            $route->params,
            [],
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
            if (in_array(HttpConf::NONCE_SPECIFIER, $values)) {
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
