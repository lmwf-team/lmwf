<?php

declare(strict_types=1);

namespace LM\WebFramework\AccessControl;

enum Clearance
{
    case ALL;

    case ADMINS;

    case VISITORS;
}