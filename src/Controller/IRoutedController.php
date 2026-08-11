<?php

declare(strict_types=1);

namespace LM\WebFramework\Controller;

use LM\WebFramework\Http\Routing\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface IRoutedController
{
    public function generateResponse(
        Route $route,
        ServerRequestInterface $request,
        array $routeParams,
        array $serverParams,
    ): ResponseInterface;
}
