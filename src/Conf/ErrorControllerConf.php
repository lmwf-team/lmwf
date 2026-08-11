<?php

declare(strict_types=1);

namespace LM\WebFramework\Conf;

final readonly class ErrorControllerConf
{
    public function __construct(
        public string $alreadyLoggedInFqcn,
        public string $defaultErrorFqcn,
        public string $methodNotSupportedFqcn,
        public string $notFoundFqcn,
        public string $notLoggedInFqcn,
    ) {
    }
}