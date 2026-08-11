<?php

namespace LM\WebFramework\Http;

use GuzzleHttp\Psr7\ServerRequest;
use LM\WebFramework\Configuration;
use LM\WebFramework\Controller\ControllerInterface;
use MF\Enum\Clearance;
use MF\Session\SessionManager;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HttpRequestHandler
{
    public function __construct(
        private Configuration $configuration,
        private ContainerInterface $container,
        private SessionManager $session,
        private TwigService $twig,
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
        $controller = $this->getController($request);

        return $controller->generateResponse($request, $this->extractRouteParams($request));
    }

    public function getController(ServerRequestInterface $request): ControllerInterface {
        $routeId = $this->extractRouteParams($request)[0];
        if (!key_exists($routeId, $this->configuration->getRoutes())) {
            return $this->container->get($this->configuration->getErrorNotFoundControllerFQCN());
        }
        $controller = $this->container->get($this->configuration->getRoutes()[$routeId]);
        if (Clearance::VISITORS === $controller->getAccessControl() && $this->session->isUserLoggedIn()) {
            return $this->container->get($this->configuration->getErrorLoggedInControllerFQCN());
        } elseif (Clearance::ADMINS === $controller->getAccessControl() && !$this->session->isUserLoggedIn()) {
            return $this->container->get($this->configuration->getErrorNotLoggedInControllerFQCN());
        }
        return $controller;
    }
}