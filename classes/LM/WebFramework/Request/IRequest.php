<?php

namespace LM\WebFramework\Request;

interface IRequest
{
    public function getUri(): string;
}