<?php

declare(strict_types=1);

namespace LMWF\Conf;

use LMWF\Conf\Http\RouteDef;

final class HttpConf
{
    const string NONCE_SPECIFIER = '{NONCE}';

    /**
     * @param array<string, list<string>> $csp
     */
    public function __construct(
        public readonly RouteDef $rootRoute,
        public readonly bool $handleExceptions,
        public readonly array $csp,
        public readonly ErrorControllerConf $errorControllers,
    ) {
    }
}
