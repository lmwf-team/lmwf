<?php

declare(strict_types=1);

namespace LM\WebFramework\Http\Routing;

use InvalidArgumentException;

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
     * @param array<string, self> $subroutes The child routes as an array of route definitions, indexed by the path segment through which they are accessed.
     * @todo What happens when an object argument has a default???
     */
    public function __construct(
        public ?string $fqcn,
        public array $roles = [],
        public array $subroutes = [],
        public int $nArgsLowerLimit = 0,
        public int $nArgsUpperLimit = 0,
        public ?string $fqcnIfParams = null,
    ) {
        foreach ($roles as $role) {
            if (!is_string($role)) {
                throw new InvalidArgumentException("A role must be a string.");
            }
        }

        foreach ($subroutes as $pathSegment => $routeDef) {
            if (!is_string($pathSegment)) {
                throw new InvalidArgumentException("Each route definition must be identified by one path segment.");
            }
            if (!$routeDef instanceof RouteDef) {
                throw new InvalidArgumentException("Routes must define a route definition.");
            }
        }

        if ($nArgsLowerLimit < 0) {
            throw new InvalidArgumentException("The minimum number of arguments for a route cannot be negative, received {$nArgsLowerLimit}.");
        } elseif ($nArgsLowerLimit > $nArgsUpperLimit) {
            throw new InvalidArgumentException("The minimum number of arguments for a route (here {$nArgsLowerLimit}) cannot be above its maximum number of arguments (here {$nArgsUpperLimit}).");
        }
    }
}
