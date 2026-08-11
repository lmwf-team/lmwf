<?php

declare(strict_types=1);

namespace LMWF\Http\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @todo Move Controller repo to Http namespace.
 */
interface IController
{
    /**
     * @todo $serverParams was added so that the error could passed to
     * and displayed by the error page, but actually it might have
     * sufficed to have created an IErrorController interface or
     * something like it.
     * @param array<string, string> $serverParams
     */
    public function generateResponse(
        ServerRequestInterface $request,
        array $serverParams,
    ): ResponseInterface;
}
