<?php

namespace LM\WebFramework;

enum Clearance
{
    case ALL;

    case ADMINS;

    case VISITORS;
}