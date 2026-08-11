<?php

declare(strict_types=1);

namespace LM\WebFramework\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface IController
{
    /**
     * @todo $serverParams was added so that the error could passed to
     * and displayed by the error page, but actually it might have
     * sufficed to have created an IErrorController interface or
     * something like it.
     */
    public function generateResponse(
        ServerRequestInterface $request,
        array $routeParams,
        array $serverParams,
    ): ResponseInterface;
}
