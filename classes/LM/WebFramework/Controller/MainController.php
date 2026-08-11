<?php

namespace LM\WebFramework\Controller;

use LM\Exception\SessionStartFailureException;
use LM\WebFramework\Routing\IRouter;

class MainController
{
    public function processRequest(IRouter $router)
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
