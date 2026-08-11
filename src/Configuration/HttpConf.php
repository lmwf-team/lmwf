<?php

declare(strict_types=1);

namespace LM\WebFramework\Configuration;

use LM\WebFramework\Http\Routing\RouteDef;

final class HttpConf
{
    public function __construct(
        public readonly RouteDef $rootRoute,
        public readonly bool $handleExceptions,
        public readonly array $csp,
        public readonly string $routeError404ControllerFQCN,
        public readonly string $routeErrorAlreadyLoggedInControllerFQCN,
        public readonly string $routeErrorNotLoggedInControllerFQCN,
        public readonly string $routeErrorMethodNotSupportedFQCN,
        public readonly string $serverErrorControllerFQCN,
    ) {
    }
}
