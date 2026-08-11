<?php

declare(strict_types=1);

namespace LM\WebFramework\Http\Routing;

use InvalidArgumentException;

/**
 * Base class for route definitions.
 *
 * It was necessary to introduce the dual concept of a route and of a route definition. This is because some parts of the application are only concerned with defining an exclusive set of URLs that are treated the same way (a route definiton) from the actual route that matches certain specific requests (a route).
 */
abstract readonly class RouteDef
{
    /**
     * @param string[] $roles
     */
    public function __construct(
        public string $fqcn,
        public array $roles = [],
    ) {
        foreach ($roles as $role) {
            if (!is_string($role)) {
                throw new InvalidArgumentException("A role must be a string.");
            }
        }
    }
}