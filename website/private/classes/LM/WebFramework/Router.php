<?php

namespace LM\WebFramework;

use LM\WebFramework\Controller\IPageController;
use LM\PersonalDataManager\Controller\HomeController;
use LM\PersonalDataManager\Controller\LoginController;

class Router
{
    public function getControllerFromRequest(): IPageController
    {
        if (!isset($_GET[PDM_PAGE])) {
            return new HomeController;
        } elseif($_GET[PDM_PAGE] === 'login') {
            return new LoginController;
        } else {
            throw new \exception;
        }
    }
}