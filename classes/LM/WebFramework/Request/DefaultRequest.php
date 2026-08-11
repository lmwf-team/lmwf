<?php

namespace LM\WebFramework\Request;

class DefaultRequest implements IRequest
{
    private $server;

    public function __construct(array $server)
    {
        $this->server = $server;
    }

    public function getUri(): string
    {
        return $this->server['REQUEST_URI'];
    }
}