<?php

declare(strict_types=1);

namespace LM\WebFramework\Http;

use LM\WebFramework\AccessControl\Clearance;
use LM\WebFramework\Configuration\Configuration;
use LM\WebFramework\Controller\Exception\AccessDenied;
use LM\WebFramework\Controller\Exception\AlreadyAuthenticated;
use LM\WebFramework\Controller\Exception\RequestedResourceNotFound;
use LM\WebFramework\Controller\Exception\RequestedRouteNotFound;
use LM\WebFramework\Controller\IResponseGenerator;
use LM\WebFramework\Session\SessionManager;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RequestWrapper;
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
     * Return a controller corresponding to the given HTTP request.
     *
     * @todo Access control should be defined in configuration.
     * @param ServerRequestInterface The HTTP request.
     * @return IResponseGenerator The Controller associated with the specified
     */
    public function findController(ServerRequestInterface $request): IResponseGenerator
    {
        $routeId = $this->getPathSegments($request->getRequestTarget())[0];

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


    /**
     * Handles the entire process of responding to an HTTP request and return an
     * HTTP response.
     */
    public function generateResponse(ServerRequestInterface $request): ResponseInterface
    {
        $response = null;

        try {
            $response = $this
                ->findController($request)
                ->generateResponse($request, RequestWrapper::getPathSegments($request->getRequestTarget()), [])
            ;
        } catch (RequestedRouteNotFound|RequestedResourceNotFound) {
            $response = $this->container->get($this->configuration->getErrorNotFoundControllerFQCN())
                ->generateResponse($request, RequestWrapper::getPathSegments($request->getRequestTarget()), [])
            ;
        } catch (AlreadyAuthenticated) {
            $response = $this->container->get($this->configuration->getErrorLoggedInControllerFQCN())
                ->generateResponse($request, RequestWrapper::getPathSegments($request->getRequestTarget()), [])
            ;
        } catch (AccessDenied) {
            $response = $this->container->get($this->configuration->getErrorNotLoggedInControllerFQCN())
                ->generateResponse($request, RequestWrapper::getPathSegments($request->getRequestTarget()), [])
            ;
        }

        return $this->addCspSources($response);
    }

    public function generateErrorResponse(RequestInterface $request, Throwable $t): ResponseInterface
    {
        $response = $this->container->get($this->configuration->getServerErrorControllerFQCN())
            ->generateResponse(
                $request,
                RequestWrapper::getPathSegments($request->getRequestTarget()),
                [
                    'throwable_hash' => hash('sha256', $t->__toString()),
                ],
            )
        ;

        return $this->addCspSources($response);
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
