<?php

declare(strict_types=1);

namespace LMWF\Conf;

use LMWF\Conf\Http\RouteDef;
use LMWF\Conf\Http\SubrouteCannotAddRoleConfException;
use LMWF\Conf\Http\UnauthorizedAttributeConfException;
use LMWF\DataStructures\AppObject;
use LMWF\Http\Controller\IRoutedController;
use UnexpectedValueException;

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
     * @param AppObject<mixed> $route The JSON-decoded route as an associative array.
     * @param null|list<string> $parentRoles The parent roles if defined, null if the current route is the root route.
     * @param bool $allowOverridingParentRoles If true, a subroute can add role its parent does not have.
     */
    public function parse(
        AppObject $route,
        ?array $parentRoles = null,
        bool $allowOverridingParentRoles = false,
    ): RouteDef {
        // Check there are no unknown keys.
        foreach ($route as $key => $_) {
            if (!in_array($key, self::ALL_KNS, strict: true)) {
                throw new UnauthorizedAttributeConfException($key);
            }
        }

        // Parse FQCN and FQCN when route is accessed with parameters.
        $fqcn = $this->parseFqcn($route, self::FQCN_KN);
        $fqcnIfParams = $this->parseFqcn($route, self::FQCN_IF_PARAMS_KN);

        $roles = null;
        if ($route->hasProperty(self::ROLES_KN) || null === $parentRoles) {
            $roles = $route->getAppList(self::ROLES_KN)->toArray();
            if (!array_is_list($roles)) {
                throw new UnexpectedValueException();
            }
            foreach ($roles as $role) {
                if (!is_string($role)) {
                    throw new UnexpectedValueException("Route definition with FQCN '$fqcn' adds a role which is not a valid string.");
                }
            }
            if (!$allowOverridingParentRoles && null !== $parentRoles) {
                foreach ($roles as $role) {
                    if (!in_array($role, $parentRoles, strict: true)) {
                        throw new SubrouteCannotAddRoleConfException($fqcn);
                    }
                }
            }
        }

        // Set subroutes.
        $subroutes = [];
        if ($route->hasProperty(self::ROUTES_KN)) {
            foreach ($route->getAppObject(self::ROUTES_KN) as $subrouteSeg => $subroute) {
                if (!$subroute instanceof AppObject) {
                    throw new UnexpectedValueException('Subroute configuration is expected to be an AppObject.');
                }
                $subroutes[$subrouteSeg] = $this->parse($subroute, $roles ?? $parentRoles);
            }
        }

        return new RouteDef(
            $fqcn,
            $roles ?? $parentRoles,
            $subroutes,
            $route->hasProperty(self::ARGS_MIN_KN) ? $route->getInt(self::ARGS_MIN_KN) : 0,
            $route->hasProperty(self::ARGS_MAX_KN) ? $route->getInt(self::ARGS_MAX_KN) : 0,
            $fqcnIfParams,
        );
    }

    /**
     * @param AppObject<mixed> $parsedRouteDefConf
     * @param non-decimal-int-string $key
     * @return ?class-string<IRoutedController>
     */
    private function parseFqcn(AppObject $parsedRouteDefConf, string $key): ?string
    {
        if ($parsedRouteDefConf->hasProperty($key)) {
            $fqcn = str_replace('.', '\\', $parsedRouteDefConf->getString($key));
            if (!class_exists($fqcn) || !is_subclass_of($fqcn, IRoutedController::class)) {
                throw new UnexpectedValueException("The route definition defined a FQCN with key '$key' and value '$fqcn' but it is either not a FQCN of an existing class, not a FQCN at all, or the FQCN of a class that does not implement IRoutedController.");
            }
            return $fqcn;
        }
        return null;
    }
}
