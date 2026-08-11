<?php

declare(strict_types=1);

namespace LMWF\Http\Controller\Issue;

use LMWF\Conf\Http\RouteDef;

final readonly class RoutingParamIssue
{
    public function __construct(
        public RoutingParamIssueCode $code,
        public RouteDef $routeDef,
        public int $actualNArgs,
    ) {
    }
}
