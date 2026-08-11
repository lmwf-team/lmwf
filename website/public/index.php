<?php

// TODO: php doc
// TODO: namespaces for autoLoadClass?
// TODO: good html, semantics, AMP, open graph, schema, accessibility WAI

// Constants definition
// TODO: change some PDM by LWF
// TODO: those constants could be replaced with an interface implemented by a 
// class belonging to PDM?
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