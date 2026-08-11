<?php

declare(strict_types=1);

namespace LM\WebFramework\Conf;

use LM\WebFramework\Http\Routing\RouteDef;

final class HttpConf
{
    const string NONCE_SPECIFIER = '{NONCE}';

    public function __construct(
        public readonly RouteDef $rootRoute,
        public readonly bool $handleExceptions,
        public readonly array $csp,
        public readonly ErrorControllerConf $errorControllers,
    ) {
    }
}
