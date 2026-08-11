<?php

namespace LM\WebFramework\Controller;

use LM\Exception\SessionStartFailureException();

class MainController
{
    public function processRequest()
    {
        $is_session_started = session_start();

        if (false === $is_session_started) {
            throw new SessionStartFailureException();
        }

        // TODO: php doc
        // TODO: namespaces for autoLoadClass?
        // TODO: good html, semantics, AMP, open graph, schema, accessibility WAI

        // Constants definition
        // These constants should be removed? The main controller should not contain
        // project-related code.
        define('PDM_PAGE', 'page');
        define('PDM_SRC', dirname(__DIR__).'/private/');
        define('PDM_CLASSES', dirname(__DIR__).'/private/classes/');
        define('PDM_USERNAME_MAX_LENGTH', 255);
        define('PDM_PASSWORD_MAX_LENGTH', 255);
        define('PDM_MAX_LENGTH', 255);

        // Autoloader's registration
        require_once(PDM_CLASSES . 'LM/Autoloader/PhpFigAutoloader.php');
        $autoloader = new LM\Autoloader\PhpFigAutoloader;
        spl_autoload_register(array($autoloader, 'autoLoadClass'));

        // Request processing
        // TODO: maybe the main controller could get these things, such as the project
        // specific router, from an interface implemented by the project? Quite useless
        // for now as the main controller only needs the router. But there are constants
        // to define.
        $router = new LM\PersonalDataManager\Routing\Router();
        $controller = $router->getControllerFromRequest();

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $controller->doGet();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->doPost($_POST);
        }
    }
}
