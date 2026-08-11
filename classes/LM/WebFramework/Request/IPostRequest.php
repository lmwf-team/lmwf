<?php

namespace LM\WebFramework\Request\IPostRequest;

interface IPostRequest extends IPostRequest
{
    public function getPostArray(): array;
}