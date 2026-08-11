<?php

// TODO: php doc
// TODO: namespaces for autoLoadClass?
// TODO: good html, semantics, accessibility WAI

// Constants definition
define('PDM_PAGE', 'page');
define('PDM_SRC', dirname(__DIR__).'/private/');
define('PDM_CLASSES', dirname(__DIR__).'/private/classes/');
define('PDM_FUNCTIONS', dirname(__DIR__).'/private/functions/');

// Autoloader's registration
require_once(PDM_FUNCTIONS . 'autoLoadClass.php');
spl_autoload_register( autoLoadClass );

// Request processing
$router = new LM\Router;
$controller = $router->getControllerFromRequest();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $controller->doGet();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->doPost();
}