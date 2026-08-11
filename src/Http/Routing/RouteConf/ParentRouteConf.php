<?php

declare(strict_types=1);

namespace LM\WebFramework\Http\Routing\RouteConf;

use InvalidArgumentException;

final readonly class ParentRouteConf
{
    public function __construct(
        public array $routes = [],
    ) {
        foreach ($routes as $id => $def) {
            if (!is_string($id)) {
                throw new InvalidArgumentException("Each route definition must be identified by one path segment.");
            }
        }
    }
}