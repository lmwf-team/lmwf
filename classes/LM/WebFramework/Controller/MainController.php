<?php

namespace LM\WebFramework\Controller;

use LM\Exception\SessionStartFailureException;
use LM\WebFramework\Routing\IRouter;
use LM\WebFramework\Request\DefaultRequest;
use LM\WebFramework\Request\DefaultPostRequest;
use LM\WebFramework\Request\IRequest;
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

        $server = $_SERVER;
        $server['REQUEST_URI'] = $_GET['page'];

        if ('GET' === $_SERVER['REQUEST_METHOD']) {
            $request = new DefaultRequest($server);
        } elseif ('POST' === $_SERVER['REQUEST_METHOD']) {
            $request = new DefaultPostRequest($server, $_POST);
        }

        if ($request instanceof IRequest) {
            $controller->doGet($request);
        } elseif ($request instanceof IPostRequest) {
            $controller->doPost($request);
        }
    }
}
