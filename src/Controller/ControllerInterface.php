<?php

declare(strict_types=1);

namespace LM\WebFramework\Controller;

use LM\WebFramework\AccessControl\Clearance;


interface ControllerInterface extends ResponseGenerator
{
    public function getAccessControl(): Clearance;
}