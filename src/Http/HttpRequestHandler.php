<?php

declare(strict_types=1);

namespace LMWF\Http;

use GuzzleHttp\Psr7\ServerRequest;
use LMWF\ErrorHandling\Log;
use LMWF\Http\Controller\Issue\ControllerIssue;
use LMWF\Http\Controller\Issue\ControllerIssueCode;
use LMWF\Http\Controller\Issue\RouteNotFoundIssue;
use LMWF\Http\Controller\Issue\RoutingParamIssue;
use LMWF\Http\DataStructures\HttpConf;
use LMWF\Http\Routing\Router;
use LMWF\Http\Security\CspNonce;
use LMWF\Session\SessionManager;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

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
        $controllerResult = $this->generateResponseFromRoute($request);

        $response = $controllerResult instanceof ControllerIssue ?
            $this->generateResponseFromRoutingIssue($request, $controllerResult) :
            $controllerResult
        ;

        return $this->addCspSources($response);
    }

    public function generateResponseFromRoute(ServerRequestInterface $request): ResponseInterface|ControllerIssue
    {
        if (!in_array($request->getMethod(), self::SUPPORTED_METHODS, strict: true)) {
            // @todo Store requested method in error
            return new ControllerIssue(ControllerIssueCode::UnsupportedMethod);
        }

        $route = $this->router->getRouteFromPath($this->httpConf->rootRoute, $request->getUri()->getPath());
        if ($route instanceof RouteNotFoundIssue) {
            return new ControllerIssue(ControllerIssueCode::ResourceNotFound);
        } elseif ($route instanceof RoutingParamIssue) {
            return new ControllerIssue(ControllerIssueCode::ResourceNotFound);
        }
        $pageConf = $route->pageConf;
        if (null === $pageConf) {
            Log::info("Request matches route with no page configuration for this number of parameters.");
            return new ControllerIssue(ControllerIssueCode::ResourceNotFound);
        }
        $controllerFqcn = $pageConf->getControllerFqcn();
        Log::info("Request matches route with controller \"{$controllerFqcn}\".");
        $controller = $this->container->get($controllerFqcn);

        // @todo Add real role system
        $userRoles = $this->session->isUserLoggedIn() ? ['ADMIN'] : ['VISITOR'];

        if (count($route->roles) > 0) {
            Log::info("Route roles are \"" . implode(",", $route->roles) . "\".");
            $isAllowed = false;
            foreach ($userRoles as $role) {
                if (in_array($role, $route->roles, strict: true)) {
                    $isAllowed = true;
                    break;
                }
            }
            if (!$isAllowed) {
                return new ControllerIssue(ControllerIssueCode::AccessDenied);
            }
        }

        $response = $controller->generateResponse(
            $route,
            $request,
        );

        return $response;
    }

    public function generateResponseFromRoutingIssue(
        ServerRequestInterface $request,
        ControllerIssue $issue,
    ): ResponseInterface {
        $fqcn = match ($issue->code) {
            ControllerIssueCode::AccessDenied => $this->httpConf->errorControllers->notLoggedInFqcn,
            ControllerIssueCode::AlreadyAuthenticated => $this->httpConf->errorControllers->alreadyLoggedInFqcn,
            ControllerIssueCode::ResourceNotFound => $this->httpConf->errorControllers->notFoundFqcn,
            ControllerIssueCode::UnsupportedMethod => $this->httpConf->errorControllers->methodNotSupportedFqcn,
            ControllerIssueCode::Unspecified => $this->httpConf->errorControllers->defaultErrorFqcn,
        };

        Log::info("Exception controller FQCN is \"{$fqcn}\".");

        $controller = $this->container->get($fqcn);
        return $controller->generateResponse($request, []);
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

    /**
     * Send the response back to the client without altering it.
     */
    public function sendResponse(ResponseInterface $response): void
    {
        http_response_code($response->getStatusCode());

        foreach ($response->getHeaders() as $headerName => $headerValues) {
            header($headerName . ': ' . implode(', ', $headerValues));
        };

        echo $response->getBody()->__toString();
    }
}
