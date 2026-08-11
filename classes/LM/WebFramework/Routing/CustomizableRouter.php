<?php

namespace LM\WebFramework\Routing;

use LM\Exception\UnfoundRouteException;
use LM\WebFramework\Controller\IPageController;
use LM\WebFramework\Routing\IRouter;

class CustomizableRouter implements IRouter
{
    private $routes;

    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    public function getControllerFromRequest(string $url): IPageController
    {
        foreach ($this->routes as $current_route => $controller) {
            if (1 === preg_match($current_route, $url)) {
                return $controller;
            }
        }
        throw new UnfoundRouteException();
    }
}