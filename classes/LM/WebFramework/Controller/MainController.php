<?php

namespace LM\WebFramework\Controller;

use LM\Exception\SessionStartFailureException;
use LM\WebFramework\Routing\DefaultRequest;
use LM\WebFramework\Routing\DefaultPostRequest;
use LM\WebFramework\Request\IRequest;
use LM\WebFramework\Routing\IRouter;
use LM\WebFramework\Routing\IRouter;
use LM\WebFramework\Request\IPostRequest;

class MainController
{
    /**
     * @todo builder for request
     */
    public function processRequest(IRouter $router)
    {
        $is_session_started = session_start();

        if (false === $is_session_started) {
            throw new SessionStartFailureException();
        }
        
        $controller = $router->getControllerFromRequest($_GET['page']);

        $request = null;

        if ('GET' === $_SERVER['REQUEST_METHOD']) {
            $request = new DefaultRequest($_SERVER);
        } elseif ('POST' === $_SERVER['REQUEST_METHOD']) {
            $request = new DefaultPostRequest($_SERVER, $_POST);
        }

        if ($request instanceof IRequest) {
            $controller->doGet($request);
        } elseif ($request instanceof IPostRequest) {
            $controller->doPost($request);
        }
    }
}
