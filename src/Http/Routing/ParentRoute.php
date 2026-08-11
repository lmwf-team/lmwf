<?php

declare(strict_types=1);

namespace LM\WebFramework\Http\Routing;

use InvalidArgumentException;

final readonly class ParentRoute extends RouteDef
{
    /**
     * @param string[] $roles
     * @param array<string, ParameterizedRoute|ParentRoute> $routes
     */
    public function __construct(
        string $fqcn,
        array $roles = [],
        public array $routes = [],
    ) {
        parent::__construct($fqcn, $roles);
        foreach ($routes as $id => $def) {
            if (!is_string($id)) {
                throw new InvalidArgumentException("Each route definition must be identified by an ID.");
            } elseif ('' === $id) {
                throw new InvalidArgumentException("The ID of a route cannot be empty.");
            } elseif (str_contains($id, '/')) {
                throw new InvalidArgumentException("The ID of a route cannot contain any forward slash (/).");
            }
        }
    }
}