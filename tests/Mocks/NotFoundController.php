<?php

declare(strict_types=1);

namespace LMWF\Tests\Mocks;

use LMWF\Http\Controller\IController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SebastianBergmann\CodeCoverage\MethodNotImplementedException;

class NotFoundController implements IController
{
    #[\Override]
    public function generateResponse(
        ServerRequestInterface $request,
        array $serverParams,
    ): ResponseInterface {
        throw new MethodNotImplementedException();
    }
}
