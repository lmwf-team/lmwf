<?php

declare(strict_types=1);

namespace LM\WebFramework\Http;

use GuzzleHttp\Psr7\ServerRequest;
use LM\WebFramework\AccessControl\Clearance;
use LM\WebFramework\Configuration;
use LM\WebFramework\Controller\ControllerInterface;
use LM\WebFramework\Controller\Exception\AccessDenied;
use LM\WebFramework\Controller\Exception\AlreadyAuthenticated;
use LM\WebFramework\Controller\Exception\RequestedResourceNotFound;
use LM\WebFramework\Controller\Exception\RequestedRouteNotFound;
use LM\WebFramework\Session\SessionManager;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

final class HttpRequestHandler
{
    public function __construct(
        private Configuration $configuration,
        private ContainerInterface $container,
        private SessionManager $session,
    ) {
    }

    /**
     * Create and send back an HTTP response to the global HTTP request.
     */
    public function processRequest(): void
    {   
        session_start();

        $request = ServerRequest::fromGlobals();

        $response = $this->generateResponse($request);

        if (302 === $response->getStatusCode()) {
            header('Location: ' . $response->getHeaderLine('Location'));
        } else {
            http_response_code($response->getStatusCode());
            echo $response->getBody()->__toString(); 
        }
    }

    /**
     * @todo Should not include the route name?
     * @return array<string>
     */
    public function extractRouteParams(ServerRequestInterface $request): array
    {
        $parts = array_map(fn ($e) => urldecode($e), explode('/', $request->getUri()->getPath()));
        if (1 === count($parts) && '' === $parts[0]) {
            return $parts;
        } else {
            return array_slice($parts, 1);
        }
    }

    /**
     * Generates a response from an HTTP request.
     * 
     * @param ServerRequestInterface $request The HTTP request.
     * @return ResponseInterface The HTTP response.
     * @todo Make sure HTTP response is valid and complete.
     */
    public function generateResponse(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $controller = $this->findController($request);
            return $controller->generateResponse($request, $this->extractRouteParams($request));
        } catch (RequestedRouteNotFound|RequestedResourceNotFound) {
            return $this->container
                ->get($this->configuration->getErrorNotFoundControllerFQCN())
                ->generateResponse($request, $this->extractRouteParams($request))
            ;
        } catch (AlreadyAuthenticated) {
            return $this->container
                ->get($this->configuration->getErrorLoggedInControllerFQCN())
                ->generateResponse($request, $this->extractRouteParams($request))
            ;
        } catch (AccessDenied) {
            return $this->container
                ->get($this->configuration->getErrorNotLoggedInControllerFQCN())
                ->generateResponse($request, $this->extractRouteParams($request))
            ;
        } catch (Throwable $t) {
            return $this->container
                ->get($this->configuration->getServerErrorControllerFQCN())
                ->generateResponse($request, array_merge($this->extractRouteParams($request), [$t]))
            ;
        }
    }

    /**
     * Find the controller associated with the request.
     * 
     * @todo Access control should be defined in configuration.
     * @param ServerRequestInterface The HTTP request.
     * @return ControllerInterface The Controller associated with the specified
     * @throws RequestedRouteNotFound If no controller matching the request were
     * found.
     * @throws AlreadyAuthenticated If the controller requires the user to be
     * authenticated, yet the user is.
     * @throws AccessDenied If the user is not authenticated, but the controller
     * requires authentication.
     * request.
     */
    public function findController(ServerRequestInterface $request): ControllerInterface
    {
        $routeId = $this->extractRouteParams($request)[0];
        if (!$this->configuration->getRoutes()->hasProperty($routeId)) {
            throw new RequestedRouteNotFound();
        }

        $controller = $this->container->get($this->configuration->getRoutes()[$routeId]);
        if (Clearance::VISITORS === $controller->getAccessControl() && $this->session->isUserLoggedIn()) {
            throw new AlreadyAuthenticated();
        } elseif (Clearance::ADMINS === $controller->getAccessControl() && !$this->session->isUserLoggedIn()) {
            throw new AccessDenied();
        }

        return $controller;
    }
}