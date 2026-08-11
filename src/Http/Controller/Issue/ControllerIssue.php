<?php

declare(strict_types=1);

namespace LMWF\Http\Controller\Issue;

final readonly class ControllerIssue
{
    public function __construct(
        public ControllerIssueCode $code,
    ) {
    }
}