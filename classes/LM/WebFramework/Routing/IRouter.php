<?php

namespace LM\WebFramework\Routing;

use LM\WebFramework\Controller\IPageController;

interface IRouter
{
    public function getControllerFromRequest(): IPageController;
}