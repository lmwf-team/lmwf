<?php

declare(strict_types=1);

namespace LM\WebFramework\Configuration;

use LM\WebFramework\DataStructures\AppObject;

final class HttpConf
{
    public function __construct(
        public readonly AppObject $rootRoute,
        public readonly bool $handleExceptions,
        public readonly ?string $cspDefaultSources,
        public readonly ?string $cspFontSources,
        public readonly ?string $cspObjectSources,
        public readonly ?string $cspStyleSources,
        public readonly string $routeError404ControllerFQCN,
        public readonly string $routeErrorAlreadyLoggedInControllerFQCN,
        public readonly string $routeErrorNotLoggedInControllerFQCN,
        public readonly string $routeErrorMethodNotSupportedFQCN,
        public readonly string $serverErrorControllerFQCN,
    ) {
    }
}
