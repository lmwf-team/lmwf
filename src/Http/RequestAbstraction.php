<?php

namespace LM\WebFramework\Controller;

use GuzzleHttp\Psr7\ServerRequest;
use LM\WebFramework\DataStructures\AppList;
use Psr\Http\Message\ServerRequestInterface;

class RequestAbstraction
{
    public const string REDIRECT_URL_KEY = 'route_params';

    private AppList $routeParams;

    public static function fromGlobals(): self
    {
        return new self(ServerRequest::fromGlobals());
    }

    public function __construct(
        private ServerRequestInterface $request,
    ) {
        $this->routeParams = new AppList(explode('/', $request->getServerParams()[self::REDIRECT_URL_KEY]));
    }

    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    public function getRouteParams(): AppList
    {
        return $this->routeParams;
    }
}
