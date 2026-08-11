<?php

namespace LM;

use LM\Controller\IPageController;
use LM\Controller\HomeController;

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