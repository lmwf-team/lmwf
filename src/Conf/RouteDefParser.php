<?php

declare(strict_types=1);

namespace LM\WebFramework\Conf;

use LM\WebFramework\Conf\Exception\SettingNotFoundException;
use LM\WebFramework\Http\Routing\Exception\InvalidRouteConfException;
use LM\WebFramework\Http\Routing\Exception\SubrouteCannotAddRoleConfException;
use LM\WebFramework\Http\Routing\Exception\UnauthorizedAttributeConfException;
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
     * @param bool $allowOverridingParentRoles If true, a subroute can add role its parent does not have.
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

        // Parse FQCN and FQCN when route is accessed with parameters.
        $fqcn = $this->parseFqcn($route, self::FQCN_KN);
        $fqcnIfParams = $this->parseFqcn($route, self::FQCN_IF_PARAMS_KN);

        // Verify and set roles.
        $roles = $parentRoles;
        if (key_exists(self::ROLES_KN, $route)) {
            $roles = $route[self::ROLES_KN];
            if (!$allowOverridingParentRoles && null !== $parentRoles) {
                foreach ($roles as $role) {
                    if (!in_array($role, $parentRoles, strict: true)) {
                        throw new SubrouteCannotAddRoleConfException($route, $role);
                    }
                }
            }
        } elseif (null === $parentRoles) {
            throw new SettingNotFoundException("The root route must define its roles.");
        }

        // Set subroutes.
        $subroutes = [];
        if (key_exists(self::ROUTES_KN, $route)) {
            foreach ($route[self::ROUTES_KN] as $subrouteSeg => $subroute) {
                $subroutes[$subrouteSeg] = $this->parse($subroute, $roles);
            }
        }

        return new RouteDef(
            $fqcn,
            $roles,
            $subroutes,
            $route[self::ARGS_MIN_KN] ?? 0,
            $route[self::ARGS_MAX_KN] ?? 0,
            $fqcnIfParams,
        );
    }

    private function parseFqcn(array $routeArray, string $key): ?string
    {
        if (key_exists($key, $routeArray)) {
            return str_replace('.', '\\', $routeArray[$key]);
        } else {
            return null;
        }
    }
}
