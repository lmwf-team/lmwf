<?php

declare(strict_types=1);

namespace LM\WebFramework\Http\Routing;

use InvalidArgumentException;
use LM\WebFramework\Http\Routing\RouteConf\ParamRouteConf;
use LM\WebFramework\Http\Routing\RouteConf\ParentRouteConf;

/**
 * A route definition.
 *
 * It was necessary to introduce the dual concept of a route and of a route
 * definition. This is because some parts of the application are only concerned
 * with defining an exclusive set of URLs that are treated the same way (a route
 * definition) from the actual route that matches certain specific requests (a
 * route).
 * In the future, these classes could be deleted for a more simple array that
 * would contain all the route definitions.
 */
final readonly class RouteDef
{
    /**
     * @param ?string $fqcn The FQCN of the controller responsible for this
     * particular partition of paths. If null, this route definition only serves
     * to set the paths of sub route definitions, set shared roles, etc.
     * @param string[] $roles Required roles to access this route.
     * @todo What happens when an object argument has a default???
     */
    public function __construct(
        public ?string $fqcn,
        public array $roles = [],
        public ParentRouteConf|ParamRouteConf $conf = new ParamRouteConf(),
    ) {
        foreach ($roles as $role) {
            if (!is_string($role)) {
                throw new InvalidArgumentException("A role must be a string.");
            }
        }
    }
}
