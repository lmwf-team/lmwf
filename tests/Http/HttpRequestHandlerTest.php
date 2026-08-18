<?php

declare(strict_types=1);

namespace LMWF\Tests\Http;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use LMWF\Conf\HttpConf;
use LMWF\Conf\ErrorControllerConf;
use LMWF\Http\Controller\IController;
use LMWF\Http\Controller\IRoutedController;
use LMWF\Http\Security\CspNonce;
use LMWF\Http\HttpRequestHandler;
use LMWF\Http\Routing\Route;
use LMWF\Conf\Http\RouteDef;
use LMWF\DataStructures\PageParam;
use LMWF\Kernel;
use LMWF\Session\SessionManager;
use LMWF\Tests\Factory\PageParamFactory;
use LMWF\Tests\Mocks\MethodNotSupportedController;
use LMWF\Tests\Mocks\NotAuthenticatedController;
use LMWF\Tests\Mocks\NotFoundController;
use LMWF\Tests\Mocks\OkController;
use LMWF\Tests\Mocks\PathController;
use LMWF\Tests\Mocks\ServerErrorController;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class HttpRequestHandlerTest extends TestCase
{
    private CspNonce $cspNonce;
    private HttpRequestHandler $handler;
    private ContainerInterface $container;

    #[\Override]
    public function setUp(): void
    {
        $this->container = Kernel::initBare([
            HttpConf::class => new HttpConf(
                new RouteDef(
                    null,
                    null,
                    ['ADMIN', 'VISITOR'],
                    subroutes: [
                        '' => new RouteDef(OkController::class, PageParamFactory::create()),
                        'my' => new RouteDef(
                            PathController::class,
                            PageParamFactory::create(),
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
                    ServerErrorController::class,
                    ServerErrorController::class,
                    MethodNotSupportedController::class,
                    NotFoundController::class,
                    NotAuthenticatedController::class,
                ),
            ),
            SessionManager::class => new SessionManager([]),
        ],);

        $this->handler = $this->getService(HttpRequestHandler::class);
        $this->cspNonce = $this->container->get(CspNonce::class);
    }

    /**
     * @template T
     * @param class-string<T> $serviceFqcn
     * @return T
     */
    public function getService(string $serviceFqcn): mixed
    {
        return $this->container->get($serviceFqcn);
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
