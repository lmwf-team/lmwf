<?php

declare(strict_types=1);

namespace LMWF\Http\Controller\Issue;

final readonly class RouteNotFoundIssue
{
    public function __construct(
        public string $nextSeg,
    ) {
    }
}
