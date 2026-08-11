<?php

namespace LM\WebFramework\Routing;

interface IUriBuilder
{
    public function getUri(string $resource_name): string;
}