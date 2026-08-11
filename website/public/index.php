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
$routes = array(
    '' => new LM\PersonalDataManager\Controller\HomeController,
    'login' => new LM\PersonalDataManager\Controller\LoginController,
    'testsp' => new LM\PersonalDataManager\Controller\TestSpController,
);
$router = new LM\WebFramework\Router($routes);
$controller = $router->getControllerFromRequest();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $controller->doGet();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->doPost();
}