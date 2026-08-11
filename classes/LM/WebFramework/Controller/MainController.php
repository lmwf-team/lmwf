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

        // TODO: php doc
        // TODO: namespaces for autoLoadClass?
        // TODO: good html, semantics, AMP, open graph, schema, accessibility WAI

        // Request processing
        // TODO: maybe the main controller could get these things, such as the project
        // specific router, from an interface implemented by the project? Quite useless
        // for now as the main controller only needs the router. But there are constants
        // to define.
        $controller = $router->getControllerFromRequest();

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $controller->doGet();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->doPost($_POST);
        }
    }
}
