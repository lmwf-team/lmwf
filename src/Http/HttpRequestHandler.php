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

class HttpRequestHandler
{
    public function __construct(
        private Configuration $configuration,
        private ContainerInterface $container,
        private SessionManager $session,
    ) {
    }

    public function processRequest(): void {   
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
    public function extractRouteParams(ServerRequestInterface $request): array {
        $parts = array_map(fn ($e) => urldecode($e), explode('/', $request->getUri()->getPath()));
        if (1 === count($parts) && '' === $parts[0]) {
            return $parts;
        } else {
            return array_slice($parts, 1);
        }
    }

    /**
     * @todo Make sure HTTP response is valid and complete.
     */
    public function generateResponse(ServerRequestInterface $request): ResponseInterface {
        try {
            $controller = $this->getController($request);
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
     * @todo Access control should be defined in configuration.
     */
    public function getController(ServerRequestInterface $request): ControllerInterface {
        $routeId = $this->extractRouteParams($request)[0];
        if (!key_exists($routeId, $this->configuration->getRoutes())) {
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