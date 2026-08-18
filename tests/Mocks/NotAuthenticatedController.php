<?php

declare(strict_types=1);

namespace LMWF\Tests\Mocks;

use GuzzleHttp\Psr7\Response;
use LMWF\Http\Controller\IController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class NotAuthenticatedController implements IController
{
    #[\Override]
    public function generateResponse(
        ServerRequestInterface $request,
        array $serverParams,
    ): ResponseInterface {
        return new Response(403);
    }
}
