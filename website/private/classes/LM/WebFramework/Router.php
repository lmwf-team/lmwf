<?php

namespace LM\WebFramework;

use LM\WebFramework\Controller\IPageController;

class Router
{
    private $routes;

    public function __construct(array $routes)
    {
        foreach ($routes as $route_name => $route_controller) {

            $correct_name = is_string($route_name);

            $correct_controller = $route_controller instanceof IPageController;

            if (!$correct_name || !$correct_controller) {
                throw new \exception; // TODO: custom exception
            }
        }

        $this->routes = $routes;
    }

    public function getControllerFromRequest(): IPageController
    {
        $controller = null;

        if (!isset($_GET[PDM_PAGE])) {
            $controller = $this->routes[null];
        } else {
            $controller = $this->routes[$_GET[PDM_PAGE]];
        }

        return $controller;
    }
}