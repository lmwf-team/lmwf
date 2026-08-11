<?php

declare(strict_types=1);

namespace LMWF\Validation\Violation;

enum ConstraintViolationCode
{
    case ARRAY_ITEM;
    case UNAUTHORIZED_TYPE;
    case UNSPECIFIED; // @todo To delete?
}
