<?php

declare(strict_types=1);

namespace LM\WebFramework\Http\Routing;

use LM\WebFramework\Http\Routing\Exception\InvalidRouteConfException;
use LM\WebFramework\Http\Routing\Exception\UnauthorizedAttributeConfException;

final readonly class RouteParser
{
    public function parseJson(string $filename): Route
    {
        $json_decoded = json_decode(file_get_contents($filename), associative: true, flags: JSON_THROW_ON_ERROR)["rootRoute"];
        return $this->parse($json_decoded);
    }

    /**
     * @param array<string, mixed> $route The JSON-decoded route as an associative array.
     * @param string[] $parentRoles The roles of the parent route, if any.
     */
    public function parse(array $route, array $parentRoles = []): Route
    {
        foreach ($route as $key => $_) {
            if (!in_array($key, ['roles', 'controller', 'minArgs', 'maxArgs', 'routes'])) {
                throw new UnauthorizedAttributeConfException("Attribute '{$key}' is unknown and not allowed in a route definition.");
            }
        }
        $roles = $route['roles'] ?? $parentRoles;
        $controller = str_replace('.', '\\', $route['controller']);
        if (key_exists('minArgs', $route) || key_exists('maxArgs', $route)) {
            if (key_exists('routes', $route)) {
                throw new InvalidRouteConfException("A route definition cannot both defines 'routes' and 'minArgs' or 'maxArgs'.");
            }
            return new ParameterizedRoute($controller, $roles, $route['minArgs'] ?? 0, $route['maxArgs'] ?? 0);
        }
        $routes = [];
        foreach ($route['routes'] ?? [] as $subRouteId => $subRoute) {
            $routes[$subRouteId] = $this->parse($subRoute, $roles);
        }
        return new ParentRoute($controller, $roles, $routes);
    }
}