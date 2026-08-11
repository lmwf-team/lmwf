<?php

namespace LM\WebFramework\Controller;

use LM\WebFramework\AccessControl\Clearance;
use LM\WebFramework\DataStructures\Page;


interface ControllerInterface extends ResponseGenerator
{
    public function getAccessControl(): Clearance;
}