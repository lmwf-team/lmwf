<?php

declare(strict_types=1);

namespace LMWF\Conf;

use InvalidArgumentException;
use LMWF\Conf\Http\RouteDef;
use LMWF\Conf\Http\SubrouteCannotAddRoleConfException;
use LMWF\Conf\Http\UnauthorizedAttributeConfException;
use LMWF\DataStructures\AppObject;
use LMWF\Http\DataStructures\PageConf;
use LMWF\ErrorHandling\ExceptionCode;
use LMWF\Http\Controller\IRoutedController;
use LMWF\Http\DataStructures\PageMetadata;
use LMWF\Repo\IRepo;
use UnexpectedValueException;

final readonly class RouteDefParser
{
    const string ARGS_MAX_KN = 'maxArgs';
    const string ARGS_MIN_KN = 'minArgs';
    const string FQCN_IF_PARAMS_KN = 'fqcnIfParams';
    const string FQCN_KN = 'fqcn';
    const string PAGE_ENT_KN = 'entConf';
    const string PAGE_ENT_REPO_FQCN_KN = 'repoFqcn';
    const string PAGE_ENT_TITLE_KN = 'title';
    const string PAGE_IS_INDEXED_KN = 'isIndexed';
    const string PAGE_IS_PART_OF_HIERARCHY_KN = 'isPartOfHierarchy';
    const string PAGE_KN = 'page';
    const string PAGE_TITLE_KN = 'title';
    const string ROLES_KN = 'roles';
    const string ROUTES_KN = 'routes';
    const array ALL_KNS = [
        self::ARGS_MAX_KN,
        self::ARGS_MIN_KN,
        self::FQCN_IF_PARAMS_KN,
        self::FQCN_KN,
        self::PAGE_ENT_KN,
        self::PAGE_ENT_REPO_FQCN_KN,
        self::PAGE_ENT_TITLE_KN,
        self::PAGE_IS_INDEXED_KN,
        self::PAGE_IS_PART_OF_HIERARCHY_KN,
        self::PAGE_KN,
        self::PAGE_TITLE_KN,
        self::ROLES_KN,
        self::ROUTES_KN,
    ];

    const string AMBIGUOUS_DEF_MSG_FMT = 'A route definition cannot define both ' . self::ROUTES_KN . ' and ' . self::ARGS_MIN_KN . ' or ' . self::ARGS_MAX_KN . '.';

    /**
     * @param string $baseUrl The URL to the app's home, without trailing slash.
     */
    public function __construct(
        private string $baseUrl,
    ) {
        if (str_ends_with($baseUrl, '/')) {
            throw new InvalidArgumentException(
                "Cannot create RouteDefParser with a leading slash, \$baseUrl '$baseUrl': given string has trailing slash.",
                ExceptionCode::CONF_ROUTEDEFPARSER_BASE_URL_MUST_NOT_HAVE_TRAILING_SLASH->value,
            );
        }
    }

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
        $pageParam = $this->parsePageConf($route);

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

        // Set sub-route definitions.
        $subRouteDefs = [];
        if ($route->hasProperty(self::ROUTES_KN)) {
            foreach ($route->getAppObject(self::ROUTES_KN) as $seg => $subroute) {
                if (!$subroute instanceof AppObject) {
                    throw new UnexpectedValueException('Subroute configuration is expected to be an AppObject.');
                }
                $subRouteDefs[$seg] = $this->parse($subroute, $roles ?? $parentRoles);
            }
        }

        return new RouteDef(
            $fqcn,
            $pageParam,
            $roles ?? $parentRoles,
            $subRouteDefs,
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

    /**
     * @param AppObject<mixed> $routeDef
     */
    private function parsePageConf(AppObject $routeDef): ?PageConf
    {
        if (!$routeDef->hasProperty(self::PAGE_KN)) {
            return null;
        }
        $pageDefConf = $routeDef[self::PAGE_KN];
        if (!$pageDefConf instanceof AppObject) {
            throw new InvalidArgumentException(
                "If present, the '{self::PAGE_KN}' property of the route definition must be a dictionnary.",
                ExceptionCode::CONF_ROUTEDEFPARSER_PAGEPARAM_CONF_WRONG_TYPE->value,
            );
        }
        return new PageConf(
            $pageDefConf->getString(self::PAGE_TITLE_KN),
            $this->baseUrl,
            $pageDefConf->getBoolOrNull(self::PAGE_IS_INDEXED_KN) ?? true,
            $pageDefConf->getBoolOrNull(self::PAGE_IS_PART_OF_HIERARCHY_KN) ?? true,
            $this->parsePageEntConf($pageDefConf),
        );
    }

    /**
     * @param AppObject<mixed> $pageConf
     */
    private function parsePageEntConf(AppObject $pageConf): ?PageMetadata
    {
        if (!$pageConf->hasProperty(self::PAGE_ENT_KN)) {
            return null;
        }
        $entConf = $pageConf->getAppObject(self::PAGE_ENT_KN);
        return new PageMetadata(
            $entConf->getString(self::PAGE_ENT_TITLE_KN),
            $entConf->getFqcn(self::PAGE_ENT_REPO_FQCN_KN, IRepo::class, convertDotsToBackslashes: true),
        );
    }
}
