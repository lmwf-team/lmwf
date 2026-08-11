<?php

declare(strict_types=1);

namespace LMWF\Conf;

final readonly class ErrorControllerConf
{
    /**
     * @param class-string<\LMWF\Http\Controller\IController> $alreadyLoggedInFqcn,
     * @param class-string<\LMWF\Http\Controller\IController> $defaultErrorFqcn,
     * @param class-string<\LMWF\Http\Controller\IController> $methodNotSupportedFqcn,
     * @param class-string<\LMWF\Http\Controller\IController> $notFoundFqcn,
     * @param class-string<\LMWF\Http\Controller\IController> $notLoggedInFqcn,
     */
    public function __construct(
        public string $alreadyLoggedInFqcn,
        public string $defaultErrorFqcn,
        public string $methodNotSupportedFqcn,
        public string $notFoundFqcn,
        public string $notLoggedInFqcn,
    ) {
    }
}
