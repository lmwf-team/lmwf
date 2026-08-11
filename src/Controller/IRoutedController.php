<?php

declare(strict_types=1);

namespace LM\WebFramework\Controller;

use LM\WebFramework\Http\Routing\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @todo Add getPage(Route $route) method.
 * @todo Errors should also be routed controllers.
 * @todo Should return either an error or a response.
 * @todo Shoud have a different method for GET and POST, this would make each
 * function lighter and would avoid duplicating
 * `if ('POST' === $request->getMethod())`.
 */
interface IRoutedController
{
    public function generateResponse(
        Route $route,
        ServerRequestInterface $request,
        array $routeParams, // @todo To remove
        array $serverParams, // @todo To remove also?
    ): ResponseInterface;
}
