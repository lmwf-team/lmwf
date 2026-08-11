<?php

namespace LM\WebFramework\Controller;

use LM\WebFramework\Clearance;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;


interface ControllerInterface
{
    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface;

    public function getAccessControl(): Clearance;
}