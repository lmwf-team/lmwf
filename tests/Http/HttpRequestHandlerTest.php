<?php

declare(strict_types=1);

namespace LM\WebFramework\Tests\Http;

use GuzzleHttp\Psr7\ServerRequest;
use LM\WebFramework\Http\HttpRequestHandler;
use LM\WebFramework\Kernel;
use LM\WebFramework\Session\SessionManager;
use PHPUnit\Framework\TestCase;

final class HttpRequestHandlerTest extends TestCase
{
    private readonly HttpRequestHandler $handler;

    public function setUp(): void
    {
        $container = Kernel::initWithRuntimeConf([
            'routeError404ControllerFQCN' => 'Error404Controller',
            'rootRoute' => [
                'fqcn' => 'HomeController'
            ],
        ], [
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

    public function testWithNonExistingRoutes(): void
    {
        $paths = [
            '/some/path',
            '/my?path=1'
        ];

        foreach ($paths as $p) {
            $request = new ServerRequest('GET', $p);
            $response = $this->handler->generateResponse($request);
            $this->assertEquals(404, $response->getStatusCode());
        }
    }
}