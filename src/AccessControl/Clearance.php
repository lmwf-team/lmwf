<?php

namespace LM\WebFramework\AccessControl;

enum Clearance
{
    case ALL;

    case ADMINS;

    case VISITORS;
}