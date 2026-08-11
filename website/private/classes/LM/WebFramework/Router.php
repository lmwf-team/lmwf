<?php

namespace LM\WebFramework;

use LM\WebFramework\Controller\IPageController;
use LM\PersonalDataManager\Controller\HomeController;
use LM\PersonalDataManager\Controller\LoginController;
use LM\PersonalDataManager\Controller\TestSpController;

class Router
{
    public function getControllerFromRequest(): IPageController
    {
        if (!isset($_GET[PDM_PAGE])) {
            return new HomeController;
        } elseif($_GET[PDM_PAGE] === 'login') {
            return new LoginController;
        } elseif($_GET[PDM_PAGE] === 'testsp') {
            return new TestSpController;
        } else {
            throw new \exception;
        }
    }
}