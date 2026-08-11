<?php

declare(strict_types=1);

namespace LMWF\Http\Controller\Issue;

enum RoutingParamIssueCode
{
    case TooManyParams;
    case NotEnoughParams;
}
