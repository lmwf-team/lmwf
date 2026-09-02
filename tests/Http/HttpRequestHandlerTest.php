<?php

declare(strict_types=1);

namespace LMWF\Tests\Http;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use LMWF\Conf\ErrorControllerConf;
use LMWF\Http\DataStructures\HttpConf;
use LMWF\Http\Controller\IController;
use LMWF\Http\Controller\IRoutedController;
use LMWF\Http\DataStructures\EntPageConf;
use LMWF\Http\Security\CspNonce;
use LMWF\Http\HttpRequestHandler;
use LMWF\Http\Routing\Route;
use LMWF\Http\DataStructures\RouteDef;
use LMWF\Http\DataStructures\PageConf;
use LMWF\Http\DataStructures\StaticPageConf;
use LMWF\Kernel;
use LMWF\Session\SessionManager;
use LMWF\Tests\Factory\PageParamFactory;
use LMWF\Tests\Mocks\Controller1;
use LMWF\Tests\Mocks\Controller2;
use LMWF\Tests\Mocks\MethodNotSupportedController;
use LMWF\Tests\Mocks\NotAuthenticatedController;
use LMWF\Tests\Mocks\NotFoundController;
use LMWF\Tests\Mocks\OkController;
use LMWF\Tests\Mocks\PathController;
use LMWF\Tests\Mocks\ServerErrorController;
use LMWF\Tests\Mocks\UserRepo;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class HttpRequestHandlerTest extends TestCase
{
    const BASE_URL = 'http://localhost';
    const HOME_PAGE_TITLE = 'Home Page';
    const HOME_PAGE_CONTROLLER = Controller1::class;
    const HOME_ROLES = ['ADMIN', 'VISITOR'];
    const HOME_PAGE_IS_INDEXED = true;
    const HOME_PAGE_IS_IN_HIERARCHY = true;
    const LOGIN_SEG = 'login';
    const LOGIN_PAGE_TITLE = 'Login';
    const LOGIN_PAGE_CONTROLLER = Controller2::class;
    const LOGIN_IS_INDEXED = false;
    const LOGIN_IS_IN_HIERARCHY = true;
    const LOGIN_ROLES = ['VISITOR'];
    const PROFILE_SEG = 'profile';
    const PROFILE_PAGE_TITLE_FMT = 'Profile of {{ name }}';
    const PROFILE_PAGE_CONTROLLER = Controller2::class;
    const PROFILE_IS_INDEXED = true;
    const PROFILE_IS_IN_HIERARCHY = true;
    const PROFILE_ROLES = self::HOME_ROLES;
    const CSP_CONF = [
        'default-src' => [
            "'self'",
            "example.com",
            "{NONCE}"
        ],
    ];

    private CspNonce $cspNonce;
    private HttpRequestHandler $handler;
    private ContainerInterface $container;
    private string $cspHeaderValue;

    #[\Override]
    public function setUp(): void
    {
        $this->container = Kernel::initBare([
            HttpConf::class => new HttpConf(
                new RouteDef(
                    new StaticPageConf(
                        self::HOME_PAGE_TITLE,
                        self::HOME_PAGE_CONTROLLER,
                        self::BASE_URL,
                        self::HOME_PAGE_IS_INDEXED,
                        self::HOME_PAGE_IS_IN_HIERARCHY,
                    ),
                    children: [
                        self::LOGIN_SEG => new RouteDef(
                            new StaticPageConf(
                                self::LOGIN_PAGE_TITLE,
                                self::LOGIN_PAGE_CONTROLLER,
                                self::BASE_URL,
                                self::LOGIN_IS_INDEXED,
                                self::LOGIN_IS_IN_HIERARCHY,
                            ),
                            roles: self::LOGIN_ROLES,
                        ),
                        self::PROFILE_SEG => new RouteDef(
                            null,
                            params: [
                                new EntPageConf(
                                    UserRepo::class,
                                    self::PROFILE_PAGE_TITLE_FMT,
                                    self::PROFILE_PAGE_CONTROLLER,
                                    self::BASE_URL,
                                    self::PROFILE_IS_INDEXED,
                                    self::PROFILE_IS_IN_HIERARCHY,
                                ),
                            ],
                            roles: self::PROFILE_ROLES,
                        )
                    ],
                    roles: self::HOME_ROLES,
                ),
                handleExceptions: true,
                csp: self::CSP_CONF,
                errorControllers: new ErrorControllerConf(
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
        $this->cspHeaderValue = "default-src 'self' example.com 'nonce-{$this->cspNonce}';";
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

    public function testValidPathsWithNoParams(): void
    {
        $absPaths = [
            '' => self::HOME_PAGE_CONTROLLER,
            '/' . self::LOGIN_SEG => self::LOGIN_PAGE_CONTROLLER,
            '/' . self::PROFILE_SEG . '/' . UserRepo::USER_ID => self::PROFILE_PAGE_CONTROLLER,
        ];

        foreach ($absPaths as $path => $controllerFqcn) {
            $request = new ServerRequest('GET', $path);
            $response = $this->handler->generateResponse($request);

            self::assertEquals($this->cspHeaderValue, $response->getHeaderLine('Content-Security-Policy'));
            self::assertEquals(200, $response->getStatusCode(), "Expected 200 for {$path}, got {$response->getStatusCode()}.");
            self::assertEquals($controllerFqcn, $response->getBody(), "Expected the page to be served by {$controllerFqcn}.");
        }
    }

    public function testInvalidPaths(): void
    {
        $absPaths = [
            '/unknown',
            '/' . self::LOGIN_SEG . '/', // This is considered an empty parameter.
            '/' . self::LOGIN_SEG . '/unknown', // Still shouldn't work, login does not expect parameters.
            '/' . self::PROFILE_SEG, // Shouldn’t work, profile does not define a page configuration fro when it receives 0 parameters
        ];

        foreach ($absPaths as $path) {
            $request = new ServerRequest('GET', $path);
            $response = $this->handler->generateResponse($request);

            self::assertEquals($this->cspHeaderValue, $response->getHeaderLine('Content-Security-Policy'));
            self::assertEquals(404, $response->getStatusCode(), "Expected HTTP 404 for {$path}, got {$response->getStatusCode()}.");
            // @todo when a route is generated by router for invalid routes
            // self::assertEquals($controllerFqcn, $response->getBody(), "Expected the page to be served by {$controllerFqcn}.");
        }
    }

    public function testHomeWithNeverSupportedMethod(): void
    {
        $neverSupportedMethods = [
            'CONNECT',
            'TRACE',
        ];

        foreach ($neverSupportedMethods as $method) {
            $request = new ServerRequest($method, self::BASE_URL);
            $response = $this->handler->generateResponse($request);
            self::assertEmpty($response->getBody()->__toString());
            self::assertEquals(501, $response->getStatusCode());
        }
    }
}
