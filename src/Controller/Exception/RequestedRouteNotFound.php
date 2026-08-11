<?php

declare(strict_types=1);

namespace LM\WebFramework\Controller\Exception;

use InvalidArgumentException;

final class RequestedRouteNotFound extends InvalidArgumentException
{
}
