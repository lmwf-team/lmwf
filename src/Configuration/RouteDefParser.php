<?php

declare(strict_types=1);

namespace LM\WebFramework\Configuration;

use LM\WebFramework\Http\Routing\Exception\InvalidRouteConfException;
use LM\WebFramework\Http\Routing\Exception\OnlyChildCannotHaveSiblingsException;
use LM\WebFramework\Http\Routing\Exception\SubRouteCannotAddRoleConfException;
use LM\WebFramework\Http\Routing\Exception\UnauthorizedAttributeConfException;
use LM\WebFramework\Http\Routing\OnlyChildParentRouteDef;
use LM\WebFramework\Http\Routing\ParameterizedRoute;
use LM\WebFramework\Http\Routing\ParentRoute;
use LM\WebFramework\Http\Routing\RouteDef;

final readonly class RouteDefParser
{
    /**
     * @param array<string, mixed> $route The JSON-decoded route as an associative array.
     * @param string[] $parentRoles The roles of the parent route, if any.
     * @param bool $allowOverridingParentRoles If true, a sub-route can add role its parent does not have.
     */
    public function parse(array $route, array $parentRoles = [], bool $allowOverridingParentRoles = false): RouteDef
    {
        foreach ($route as $key => $_) {
            if (!in_array($key, ['roles', 'fqcn', 'minArgs', 'maxArgs', 'route', 'routes'])) {
                throw new UnauthorizedAttributeConfException("Attribute '{$key}' is unknown and not allowed in a route definition.");
            }
        }
        $roles = $route['roles'] ?? $parentRoles;
        foreach ($roles as $role) {
            if (!in_array($role, $parentRoles, strict: true) && !$allowOverridingParentRoles) {
                throw new SubRouteCannotAddRoleConfException("Unless explicitely authorized, a sub-route cannot add roles its parent does not have. Child node requires role '{$role}'.");
            }
        }
        $fqcn = str_replace('.', '\\', $route['fqcn']);
        if (key_exists('minArgs', $route) || key_exists('maxArgs', $route)) {
            if (key_exists('routes', $route)) {
                throw new InvalidRouteConfException("A route definition cannot both defines 'routes' and 'minArgs' or 'maxArgs'.");
            }
            if (key_exists('route', $route)) {
                throw new InvalidRouteConfException("A route definition cannot both defines 'route' and 'minArgs' or 'maxArgs'.");
            }
            return new ParameterizedRoute($fqcn, $roles, $route['minArgs'] ?? 0, $route['maxArgs'] ?? 0);
        }
        if (key_exists('route', $route)) {
            if (key_exists('routes', $route)) {
                throw new OnlyChildCannotHaveSiblingsException("A route definition cannot both defines 'routes' and 'route'.");
            }
            $onlyChildRouteDef = $this->parse($route['route'], $roles);
            return new OnlyChildParentRouteDef($fqcn, $onlyChildRouteDef, $roles);
        }
        $routes = [];
        foreach ($route['routes'] ?? [] as $subRouteId => $subRoute) {
            $routes[$subRouteId] = $this->parse($subRoute, $roles);
        }
        return new ParentRoute($fqcn, $roles, $routes);
    }
}
