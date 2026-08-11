<?php

declare(strict_types=1);

namespace LMWF\Http\Controller\Issue;

use LMWF\Conf\Http\RouteDef;

final readonly class RouteNotFoundIssue
{
    public function __construct(
        public string $nextSeg,
    ) {
    }
}