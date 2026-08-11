<?php

declare(strict_types=1);

namespace LMWF\Http\Exception;

interface IHttpException
{
    public function getStatusCode(): int;
}
