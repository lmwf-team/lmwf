<?php

declare(strict_types=1);

namespace LM\WebFramework\Http\Routing;

use InvalidArgumentException;

/**
 * A RouteDef that allows an associative array of sub routes, each identified
 * with exactly one path segment.
 */
final readonly class ParentRoute extends RouteDef
{
    /**
     * @param string[] $roles
     * @param array<string, RouteDef> $routes The child routes. The key of the
     * array is the path segment associated with the route.
     */
    public function __construct(
        string $fqcn,
        array $roles = [],
        public array $routes = [],
    ) {
        parent::__construct($fqcn, $roles);
        foreach ($routes as $id => $def) {
            if (!is_string($id)) {
                throw new InvalidArgumentException("Each route definition must be identified by one path segment.");
            } elseif ('' === $id) {
                throw new InvalidArgumentException("The path segment identifying a route cannot be empty.");
            } elseif (str_contains($id, '/')) {
                throw new InvalidArgumentException("The path segment identifying a route cannot contain any forward slash (/).");
            }
        }
    }
}
