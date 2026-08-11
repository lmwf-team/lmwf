<?php

// TODO: php doc
// TODO: namespaces for autoLoadClass?
// TODO: good html, semantics, AMP, open graph, schema, accessibility WAI

// Constants definition
define('PDM_PAGE', 'page');
define('PDM_SRC', dirname(__DIR__).'/private/');
define('PDM_CLASSES', dirname(__DIR__).'/private/classes/');

// Autoloader's registration
require_once(PDM_CLASSES . 'LM/Autoloader/PhpFigAutoloader.php');
$autoloader = new LM\Autoloader\PhpFigAutoloader;
spl_autoload_register(array($autoloader, 'autoLoadClass'));

// Request processing
$router = new LM\WebFramework\Router;
$controller = $router->getControllerFromRequest();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $controller->doGet();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->doPost();
}