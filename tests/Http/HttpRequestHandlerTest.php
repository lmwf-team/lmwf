<?php

declare(strict_types=1);

namespace LM\WebFramework\Tests\Http;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\ServerRequest;
use LM\WebFramework\Configuration\Configuration;
use LM\WebFramework\Http\HttpRequestHandler;
use LM\WebFramework\Http\Router;
use LM\WebFramework\Kernel;
use LM\WebFramework\Session\SessionManager;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

final class HttpRequestHandlerTest extends TestCase
{
    private readonly HttpRequestHandler $handler;

    public function setUp(): void
    {
        $container = Kernel::initWithRuntimeConf([], [
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
        $router = new Router(new Configuration([]));

        foreach ($neverSupportedMethods as $method) {
            $request = new ServerRequest($method, '');
            $response = $this->handler->generateResponse($request);
            $this->assertEmpty($response->getBody()->__toString());
            $this->assertEquals(501, $response->getStatusCode());
        }
    }
}