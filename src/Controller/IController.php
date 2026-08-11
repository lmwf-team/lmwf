<?php

declare(strict_types=1);

namespace LM\WebFramework\Controller;

use LM\WebFramework\AccessControl\Clearance;

/**
 * A Controller is a Response Generator for requests matching a defined
 * route.
 */
interface IController extends IResponseGenerator
{
}