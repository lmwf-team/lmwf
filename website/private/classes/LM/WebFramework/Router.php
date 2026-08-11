<?php

namespace LM\WebFramework;

use LM\WebFramework\Controller\IPageController;
use LM\PersonalDataManager\Controller\HomeController;

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