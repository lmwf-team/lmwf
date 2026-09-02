<?php

declare(strict_types=1);

namespace LMWF\Tests\Mocks;

use GuzzleHttp\Psr7\Response;
use LMWF\Http\Controller\IRoutedController;
use LMWF\Http\Routing\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SebastianBergmann\CodeCoverage\MethodNotImplementedException;

class MockController implements IRoutedController
{
    #[\Override]
    public function generateResponse(
        Route $route,
        ServerRequestInterface $request,
    ): ResponseInterface {
        return new Response(body: static::class);
    }
}
