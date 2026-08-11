<?php

namespace LM\WebFramework\Controller;

use LM\WebFramework\AccessControl\Clearance;


interface ControllerInterface extends ResponseGenerator
{
    public function getAccessControl(): Clearance;
}