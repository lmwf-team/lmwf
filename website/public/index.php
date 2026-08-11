<?php

session_start();

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

function setUsername (string $username)
{
    $_SESSION['username'] = $username;
}

function getUsername(): ?string
{
    return isset($_SESSION['username']) ? $_SESSION['username'] : null;
}

function isConnected(): bool
{
    return isset($_SESSION['username']) && $_SESSION['username'] !== null;
}