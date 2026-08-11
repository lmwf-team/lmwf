<?php

session_start();

require_once(private_root().'lib/f_currentlyconnecteduser/f_currentlyconnecteduser.php');

// TODO: check coding style

$method_prefix = strtolower($_SERVER['REQUEST_METHOD']).'_';

$view = getPage($_GET) !== null ? getPage($_GET).'.php' : 'index.php';

require(views().$method_prefix.$view);

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