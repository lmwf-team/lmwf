<?php

declare(strict_types=1);

namespace LMWF\Http\Controller\Issue;

enum ControllerIssueCode
{
    case AlreadyAuthenticated;
    case ResourceNotFound;
    case Unspecified;
    case AccessDenied;
    case UnsupportedMethod;
}
