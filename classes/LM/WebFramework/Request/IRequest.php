<?php

namespace LM\WebFramework\Request;

interface IRequest
{
    public function getUri(): string;
    public function isGet(): bool;
    public function isPost(): bool;
}