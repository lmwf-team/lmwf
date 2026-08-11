<?php

namespace LM;

class Router
{
    public function getControllerFromRequest(): IPageController
    {
        if (!isset($_GET[PDM_PAGE])) {
            return new HomeController;
        } else {
            throw new \exception;
        }
    }
}