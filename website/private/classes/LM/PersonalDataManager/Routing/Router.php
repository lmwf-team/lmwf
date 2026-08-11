<?php

namespace LM\PersonalDataManager\Routing;

use LM\PersonalDataManager\Controller\HomeController;
use LM\PersonalDataManager\Controller\LoginController;
use LM\PersonalDataManager\Controller;
use LM\WebFramework\Routing\IRouter;

class Router implements IRouter
{
    public function getControllerFromRequest(): IPageController
    {
        // TODO: what if $_GET['page'] is not set?
        if ('home' === $_GET['page']) {
            return new HomeController;
        } elseif ('login' === $_GET['page']) {
            'login' => new LoginController;
        } elseif ('testsp' === $_GET['page']) {
            return new TestSpController;
        } else {
            throw new \exception; // TODO
        }
    }
}