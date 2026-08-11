<?php

namespace LM\WebFramework\Http\Exception;

interface IHttpException
{
    public function getStatusCode(): int;
}