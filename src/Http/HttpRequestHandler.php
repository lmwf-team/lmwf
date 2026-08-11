<?php

declare(strict_types=1);

namespace LM\WebFramework\Http;

use LengthException;
use LM\WebFramework\AccessControl\Clearance;
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
        private SessionManager $session,
    ) {
    }

    /**
     * Handles the entire process of responding to an HTTP request and return an
     * HTTP response.
     */
    public function generateResponse(ServerRequestInterface $request): ResponseInterface
    {
        $pathSegments = $this->getPathSegments($request->getRequestTarget());

        try {
            $controllerRoute = $this->configuration->getControllerFqcn($pathSegments);
            $controller = $this->container->get($controllerRoute['class']);

            /**
             * @todo Define clearance in config
             */
            if (Clearance::VISITORS === $controller->getAccessControl() && $this->session->isUserLoggedIn()) {
                throw new AlreadyAuthenticated();
            } elseif (Clearance::ADMINS === $controller->getAccessControl() && !$this->session->isUserLoggedIn()) {
                throw new AccessDenied();
            }
    
            return $this->addCspSources($controller->generateResponse(
                $request,
                $pathSegments,
                [],
            ));
        } catch (SettingNotFoundException $e) {
            throw new RequestedRouteNotFound(
                'The requested route does not exist.',
                self::UNEXISTING_ROUTE,
                $e,
            );
        }
    }

    public function generateErrorResponse(RequestInterface $request, Throwable $t): ResponseInterface
    {
        $pathSegments = self::getPathSegments($request->getRequestTarget());
        
        if ($t instanceof RequestedRouteNotFound || $t instanceof RequestedResourceNotFound) {
            $response = $this->container->get($this->configuration->getErrorNotFoundControllerFQCN())
                ->generateResponse($request,$pathSegments, [])
            ;
        } elseif ($t instanceof AlreadyAuthenticated) {
            $response = $this->container->get($this->configuration->getErrorLoggedInControllerFQCN())
                ->generateResponse($request,$pathSegments, [])
            ;
        } elseif ($t instanceof AccessDenied) {
            $response = $this->container->get($this->configuration->getErrorNotLoggedInControllerFQCN())
                ->generateResponse($request,$pathSegments, [])
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
     * The returned array is of length 1 at the very least, or an
     * LengthException is thrown.
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
        if (count($parts) === 0) {
            throw new LengthException('Path should must at least one segment.');
        }
        return $parts;
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
