<?php

declare(strict_types=1);

namespace LMWF\Tests\Http;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use LMWF\Conf\HttpConf;
use LMWF\Conf\ErrorControllerConf;
use LMWF\Http\Controller\Exception\AlreadyAuthenticated;
use LMWF\Http\Controller\IController;
use LMWF\Http\Controller\IRoutedController;
use LMWF\Http\Security\CspNonce;
use LMWF\Http\HttpRequestHandler;
use LMWF\Http\Routing\Route;
use LMWF\Conf\Http\RouteDef;
use LMWF\Kernel;
use LMWF\Session\SessionManager;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class HttpRequestHandlerTest extends TestCase
{
    private CspNonce $cspNonce;
    private HttpRequestHandler $handler;

    public function setUp(): void
    {
        $container = Kernel::initBare([
            HttpConf::class => new HttpConf(
                new RouteDef(
                    null,
                    ['ADMIN', 'VISITOR'],
                    subroutes: [
                        '' => new RouteDef(HomeController::class),
                        'my' => new RouteDef(
                            MyController::class,
                            ['VISITOR']
                        ),
                    ],
                ),
                true,
                [
                    'default-src' => [
                        "'self'",
                        "example.com",
                        "{NONCE}"
                    ],
                ],
                new ErrorControllerConf(
                    AlreadyAuthenticated::class,
                    ServerErrorController::class,
                    MethodNotSupportedController::class,
                    ResourceNotFoundController::class,
                    NotAuthenticatedController::class,
                ),
            ),
            SessionManager::class => new SessionManager([]),
        ],);

        $this->handler = $container->get(HttpRequestHandler::class);
        $this->cspNonce = $container->get(CspNonce::class);
    }

    public function testCspHeaders(): void
    {
        $absPaths = [
            '/my',
            '/',
            '',
        ];

        foreach ($absPaths as $p) {
            $request = new ServerRequest('GET', $p);
            $response = $this->handler->generateResponse($request);
            self::assertEquals("default-src 'self' example.com 'nonce-{$this->cspNonce}';", $response->getHeaderLine('Content-Security-Policy'));
        }
    }

    public function testWithExistingRoutes(): void
    {
        $absPaths = [
            '/my',
            '/',
            '',
        ];

        foreach ($absPaths as $p) {
            $request = new ServerRequest('GET', $p);
            $response = $this->handler->generateResponse($request);
            self::assertEquals(200, $response->getStatusCode(), "Expected 200 for {$p}, got {$response->getStatusCode()}.");
        }
    }

    public function testWithNeverSupportedMethod(): void
    {
        $neverSupportedMethods = [
            'CONNECT',
            'TRACE',
        ];

        foreach ($neverSupportedMethods as $method) {
            $request = new ServerRequest($method, '');
            $response = $this->handler->generateResponse($request);
            self::assertEmpty($response->getBody()->__toString());
            self::assertEquals(501, $response->getStatusCode());
        }
    }

    public function testWithNonExistingRoutes(): void
    {
        $paths = [
            '/some/path',
            '/my-bad?path=1'
        ];

        foreach ($paths as $p) {
            $request = new ServerRequest('GET', $p);
            $response = $this->handler->generateResponse($request);
            self::assertEquals(404, $response->getStatusCode(), "Expected 404 for {$p}, got {$response->getStatusCode()}.");
        }
    }
}

final class ResourceNotFoundController implements IController
{
    public function generateResponse(
        ServerRequestInterface $request,
        array $serverParams,
    ): ResponseInterface {
        return new Response(404);
    }
}

final class MethodNotSupportedController implements IController
{
    public function generateResponse(
        ServerRequestInterface $request,
        array $serverParams,
    ): ResponseInterface {
        return new Response(501);
    }
}

final class NotAuthenticatedController implements IController
{
    public function generateResponse(
        ServerRequestInterface $request,
        array $serverParams,
    ): ResponseInterface {
        return new Response(403);
    }
}

final class ServerErrorController implements IController
{
    public function generateResponse(
        ServerRequestInterface $request,
        array $serverParams,
    ): ResponseInterface {
        return new Response(500);
    }
}

final class HomeController implements IRoutedController
{
    public function generateResponse(
        Route $route,
        ServerRequestInterface $request,
    ): ResponseInterface {
        return new Response(200);
    }
}

final class MyController implements IRoutedController
{
    public function generateResponse(
        Route $route,
        ServerRequestInterface $request,
    ): ResponseInterface {
        return new Response(200, body: $route->getPath());
    }
}
