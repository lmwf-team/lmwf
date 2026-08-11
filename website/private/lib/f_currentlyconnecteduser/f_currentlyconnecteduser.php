<?php

/*
 * session_start() must have been called for this file to be used!
 * 
 * @author Matl
 */

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

function logOut(): void
{
    $_SESSION['username'] = null;
}