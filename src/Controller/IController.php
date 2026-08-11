<?php

declare(strict_types=1);

namespace LM\WebFramework\Controller;

use LM\WebFramework\AccessControl\Clearance;


interface IController extends IResponseGenerator
{
    public function getAccessControl(): Clearance;
}