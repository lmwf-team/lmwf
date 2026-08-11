<?php

declare(strict_types=1);

namespace LM\WebFramework\Http\Exception;

interface IHttpException
{
    public function getStatusCode(): int;
}