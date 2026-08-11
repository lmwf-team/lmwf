<?php

declare(strict_types=1);

namespace LM\WebFramework\Configuration;

use LM\WebFramework\Configuration\Exception\SettingNotFoundException;
use LM\WebFramework\Http\Routing\Exception\InvalidRouteConfException;
use LM\WebFramework\Http\Routing\Exception\OnlyChildCannotHaveSiblingsException;
use LM\WebFramework\Http\Routing\Exception\SubRouteCannotAddRoleConfException;
use LM\WebFramework\Http\Routing\Exception\UnauthorizedAttributeConfException;
use LM\WebFramework\Http\Routing\RouteParam\ParameterizedRouteParam;
use LM\WebFramework\Http\Routing\RouteParam\ParentRouteParam;
use LM\WebFramework\Http\Routing\RouteDef;

final readonly class RouteDefParser
{
    const string ARGS_MAX_KN = 'maxArgs';
    const string ARGS_MIN_KN = 'minArgs';
    const string FQCN_IF_PARAMS_KN = 'fqcnIfParams';
    const string FQCN_KN = 'fqcn';
    const string ROLES_KN = 'roles';
    const string ROUTES_KN = 'routes';
    const array ALL_KNS = [
        self::ARGS_MAX_KN,
        self::ARGS_MIN_KN,
        self::FQCN_IF_PARAMS_KN,
        self::FQCN_KN,
        self::ROLES_KN,
        self::ROUTES_KN,
    ];

    const string AMBIGUOUS_DEF_MSG_FMT = 'A route definition cannot both defines ' . self::ROUTES_KN . ' and ' . self::ARGS_MIN_KN . ' or ' . self::ARGS_MAX_KN . '.';

    /**
     * @param array<string, mixed> $route The JSON-decoded route as an associative array.
     * @param null|string[] $parentRoles The parent roles if defined, null if the current route is the root route.
     * @param bool $allowOverridingParentRoles If true, a sub-route can add role its parent does not have.
     */
    public function parse(
        array $route,
        ?array $parentRoles = null,
        bool $allowOverridingParentRoles = false,
    ): RouteDef {
        // Check there are no unknown keys.
        foreach ($route as $key => $_) {
            if (!in_array($key, self::ALL_KNS)) {
                throw new UnauthorizedAttributeConfException($key);
            }
        }

        // Verify and set roles.
        if (key_exists(self::ROLES_KN, $route)) {
            $roles = $route[self::ROLES_KN];
            if (!$allowOverridingParentRoles && null !== $parentRoles) {
                foreach ($roles as $role) {
                    if (!in_array($role, $parentRoles, strict: true)) {
                        throw new SubRouteCannotAddRoleConfException($route, $role);
                    }
                }
            }
        } elseif (null === $parentRoles) {
            throw new SettingNotFoundException("The root route must define its roles.");
        } else {
            $roles = $parentRoles;
        }

        // Parse FQCN.
        $fqcn = $this->parseFqcn($route[self::FQCN_KN]);

        if (key_exists(self::ARGS_MIN_KN, $route) || key_exists(self::ARGS_MAX_KN, $route)) {
            if (key_exists(self::ROUTES_KN, $route)) {
                throw new InvalidRouteConfException(self::AMBIGUOUS_DEF_MSG_FMT);
            }
            $fqcnIfParams = key_exists(self::FQCN_IF_PARAMS_KN, $route) ? $this->parseFqcn($route[self::FQCN_IF_PARAMS_KN]) : null;
            return new RouteDef(
                $fqcn,
                $roles,
                new ParameterizedRouteParam(
                    $route[self::ARGS_MIN_KN] ?? 0,
                    $route[self::ARGS_MAX_KN] ?? 0,
                    $fqcnIfParams,
                ),
            );
        }

        $routes = [];
        foreach ($route[self::ROUTES_KN] ?? [] as $subRouteId => $subRoute) {
            $routes[$subRouteId] = $this->parse($subRoute, $roles);
        }
        return new RouteDef(
            $fqcn,
            $roles,
            new ParentRouteParam($routes),
        );
    }

    private function parseFqcn(string $fqcn): string
    {
        return str_replace('.', '\\', $fqcn);
    }
}
