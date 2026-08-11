<?php

namespace LM\WebFramework\Controller;

use LM\Exception\SessionStartFailureException;
use LM\PersonalDataManager\Routing\Router;

class MainController
{
    public function processRequest(Router $router)
    {
        $is_session_started = session_start();

        if (false === $is_session_started) {
            throw new SessionStartFailureException();
        }
        
        $controller = $router->getControllerFromRequest();

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $controller->doGet();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->doPost($_POST);
        }
    }
}
