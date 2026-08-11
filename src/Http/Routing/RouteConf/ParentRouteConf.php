<?php

declare(strict_types=1);

namespace LM\WebFramework\Http\Routing\RouteConf;

use InvalidArgumentException;
use LM\WebFramework\Http\Routing\RouteDef;

final readonly class ParentRouteConf
{
    public function __construct(
        public array $routes = [],
    ) {
        foreach ($routes as $id => $def) {
            if (!is_string($id)) {
                throw new InvalidArgumentException("Each route definition must be identified by one path segment.");
            }
            if (!$def instanceof RouteDef) {
                throw new InvalidArgumentException("Routes must define a route definition.");
            }
        }
    }
}
