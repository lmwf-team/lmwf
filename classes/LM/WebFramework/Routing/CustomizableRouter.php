<?php

namespace LM\WebFramework\Routing;

use LM\WebFramework\Controller\IPageController;
use LM\WebFramework\Routing\IRouter;

class CustomizableRouter implements IRouter
{
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function getControllerFromRequest(string $route): IPageController
    {
        return $this->config[$route];
    }
}