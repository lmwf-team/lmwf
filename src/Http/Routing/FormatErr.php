<?php

declare(strict_types=1);

namespace LMWF\Http\Routing;

enum FormatErr
{
    case EntNotFound;
    case MatchErr;
    case PropertyNameIsNotStr;
    case StrReplaceUnknownError;
}
