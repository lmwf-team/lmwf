<?php

declare(strict_types=1);

namespace LM\WebFramework\Http;

use GuzzleHttp\Psr7\ServerRequest;
use LM\WebFramework\AccessControl\Clearance;
use LM\WebFramework\Configuration;
use LM\WebFramework\Controller\Exception\AccessDenied;
use LM\WebFramework\Controller\Exception\AlreadyAuthenticated;
use LM\WebFramework\Controller\Exception\RequestedResourceNotFound;
use LM\WebFramework\Controller\Exception\RequestedRouteNotFound;
use LM\WebFramework\Controller\IResponseGenerator;
use LM\WebFramework\Logging\Logger;
use LM\WebFramework\Session\SessionManager;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

final class HttpRequestHandler
{
    public function __construct(
        private Configuration $configuration,
        private ContainerInterface $container,
        private Logger $logger,
        private SessionManager $session,
    ) {
    }

    /**
     * Handles the entire process of responding to an HTTP request with an HTTP
     * response.
     * 
     * Start the session, instantiate a request object from the PHP globals,
     * pass it to various methods and send back the resulting response and
     * headers.
     */
    public function processRequest(): void
    {   
        session_start();

        $request = ServerRequest::fromGlobals();

        $controller = $this->findController($request);

        $response = $controller->generateResponse($request, $this->extractRouteParams($request));

        header("Content-Security-Policy: default-src {$this->configuration->getCSPDefaultSources()}; object-src {$this->configuration->getCSPObjectSources()}");

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
     * Return a controller corresponding to the given HTTP request.
     * 
     * @todo Access control should be defined in configuration.
     * @param ServerRequestInterface The HTTP request.
     * @return IResponseGenerator The Controller associated with the specified
     */
    public function findController(ServerRequestInterface $request): IResponseGenerator
    {
        try {
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
        } catch (RequestedRouteNotFound|RequestedResourceNotFound) {
            return $this->container->get($this->configuration->getErrorNotFoundControllerFQCN());
        } catch (AlreadyAuthenticated) {
            return $this->container->get($this->configuration->getErrorLoggedInControllerFQCN());
        } catch (AccessDenied) {
            return $this->container->get($this->configuration->getErrorNotLoggedInControllerFQCN());
        } catch (Throwable $t) {
            $this->logger->log($t->__toString());
            return $this->container->get($this->configuration->getServerErrorControllerFQCN());
        }
    }
}