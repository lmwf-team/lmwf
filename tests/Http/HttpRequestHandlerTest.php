<?php

declare(strict_types=1);

namespace LM\WebFramework\Tests\Http;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use LM\WebFramework\Configuration\HttpConf;
use LM\WebFramework\Controller\Exception\AlreadyAuthenticated;
use LM\WebFramework\Controller\IController;
use LM\WebFramework\Controller\IRoutedController;
use LM\WebFramework\Http\HttpRequestHandler;
use LM\WebFramework\Http\Routing\OnlyChildParentRouteDef;
use LM\WebFramework\Http\Routing\ParameterizedRoute;
use LM\WebFramework\Http\Routing\ParentRoute;
use LM\WebFramework\Http\Routing\Route;
use LM\WebFramework\Kernel;
use LM\WebFramework\Session\SessionManager;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class HttpRequestHandlerTest extends TestCase
{
    private HttpRequestHandler $handler;

    public function setUp(): void
    {
        $container = Kernel::initBare([
            HttpConf::class => new HttpConf(
                new ParentRoute(
                    HomeController::class,
                    [
                        'ADMIN',
                        'VISITOR'
                    ],
                    [
                        'my' => new ParameterizedRoute(
                            MyController::class,
                            [
                                'VISITOR'
                            ]
                        ),
                        'only-child-parent' => new OnlyChildParentRouteDef(
                            MyController::class,
                            new ParameterizedRoute(
                                MyController::class,
                                [],
                                1,
                                1,
                            )
                        )
                    ]
                ),
                true,
                null,
                null,
                null,
                null,
                ResourceNotFoundController::class,
                AlreadyAuthenticated::class,
                ResourceNotFoundController::class,
                MethodNotSupportedController::class,
                ServerErrorController::class,
            ),
            SessionManager::class => new SessionManager([]),
        ]);
        $this->handler = $container->get(HttpRequestHandler::class);
    }

    public function testWithNeverSupportedMethod(): void
    {
        $neverSupportedMethods = [
            'CONNECT',
            'TRACE',
            'something',
        ];

        foreach ($neverSupportedMethods as $method) {
            $request = new ServerRequest($method, '');
            $response = $this->handler->generateResponse($request);
            $this->assertEmpty($response->getBody()->__toString());
            $this->assertEquals(501, $response->getStatusCode());
        }
    }

    public function testWithExistingRoutes(): void
    {
        $paths = [
            '/',
            '',
            'my',
            // 'my/',
            // 'my//',
            // 'my//?',
            // 'my//?fb_id',
            // 'my//?fb_id=a',
            // 'my//?fb_id=a&',
            // 'my//?fb_id=a&b=',
            // 'my//?fb_id=a&b=x',
            '/my',
            // '/my/',
            // '/my//',
            // '/my//?',
            // '/my//?fb_id',
            // '/my//?fb_id=a',
            // '/my//?fb_id=a&',
            // '/my//?fb_id=a&b=',
            // '/my//?fb_id=a&b=x',
        ];

        foreach ($paths as $p) {
            $request = new ServerRequest('GET', $p);
            $response = $this->handler->generateResponse($request);
            $this->assertEquals(200, $response->getStatusCode(), "Expected 200 for {$p}, got {$response->getStatusCode()}.");
        }
    }

    public function testOnlyChild(): void
    {
        $path = '/only-child-parent/child';

        $request = new ServerRequest('GET', $path);
        $response = $this->handler->generateResponse($request);
        $this->assertEquals(200, $response->getStatusCode(), "Expected 200 for {$path}, got {$response->getStatusCode()}.");
        $this->assertEquals($path, $response->getBody()->getContents());
    }

    public function testOnlyChildParent(): void
    {
        $path = '/only-child-parent';

        $request = new ServerRequest('GET', $path);
        $response = $this->handler->generateResponse($request);
        $this->assertEquals(200, $response->getStatusCode(), "Expected 200 for {$path}, got {$response->getStatusCode()}.");
        $this->assertEquals($path, $response->getBody()->getContents());
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
            $this->assertEquals(404, $response->getStatusCode(), "Expected 404 for {$p}, got {$response->getStatusCode()}.");
        }
    }
}

final class ResourceNotFoundController implements IController
{
    public function generateResponse(
        ServerRequestInterface $request,
        array $routeParams,
        array $serverParams,
    ): ResponseInterface {
        return new Response(404);
    }
}

final class MethodNotSupportedController implements IController
{
    public function generateResponse(
        ServerRequestInterface $request,
        array $routeParams,
        array $serverParams,
    ): ResponseInterface {
        return new Response(501);
    }
}

final class ServerErrorController implements IController
{
    public function generateResponse(
        ServerRequestInterface $request,
        array $routeParams,
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
        array $routeParams,
        array $serverParams,
    ): ResponseInterface {
        return new Response(200);
    }
}

final class MyController implements IRoutedController
{
    public function generateResponse(
        Route $route,
        ServerRequestInterface $request,
        array $routeParams,
        array $serverParams,
    ): ResponseInterface {
        return new Response(200, body: $route->getPath());
    }
}
