<?php

declare(strict_types=1);

namespace LM\WebFramework\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;


interface ResponseGenerator
{
    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface;
}