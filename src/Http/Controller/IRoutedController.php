<?php

declare(strict_types=1);

namespace LMWF\Http\Controller;

use LMWF\Http\Controller\Issue\ControllerIssue;
use LMWF\Http\Routing\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @todo Add create(Route $route) method?
 * @todo Errors should also be routed controllers?
 * @todo Shoud have a different method for GET and POST? This could make each
 * function lighter and would avoid duplicating
 * `if ('POST' === $request->getMethod())`.
 */
interface IRoutedController
{
    public function generateResponse(
        Route $route,
        ServerRequestInterface $request,
    ): ResponseInterface|ControllerIssue;
}
