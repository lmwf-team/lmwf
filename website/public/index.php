<?php

session_start();

require_once(private_root().'lib/f_currentlyconnecteduser/f_currentlyconnecteduser.php');
require_once(private_root().'views/I_Controller.php');

// TODO: check coding style

$controllerName = isset($_GET['page']) ? $_GET['page'] : 'index';

$controller = getController($controllerName);

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $controller->get();
        break;
    
    case 'POST':
        $controller->post();
        break;
}

function getController(string $controllerName): I_Controller
{
    $controller;
    switch ($controllerName) {
        case 'index':
            require_once(views().'Index.php');
            $controller = new Index();
            break;

        case 'login':
            require_once(views().'Login.php');
            $controller = new Controller();
            break;
        
        case 'logout':
            require_once(views().'Logout.php');
            $controller = new Logout();
            break;
    }
    return $controller;
}

function views(): string
{
    return '../private/views/';
}

function getPage(array $get): ?string
{
    if (!isset($get['page'])) {
        return null;
    } else {
        return $get['page'];
    }
}

function translate(string $string): string
{
    return $string;
}

function private_root(): string
{
    return '../private/';
}

function lib_root(): string
{
    return '../private/lib/';
}