<?php

declare(strict_types=1);

namespace LMWF\Conf;

use InvalidArgumentException;
use LMWF\Http\DataStructures\RouteDef;
use LMWF\Conf\Http\SubRouteCannotAddRoleConfException;
use LMWF\Conf\Http\UnauthorizedAttributeConfException;
use LMWF\DataStructures\AppObject;
use LMWF\DataStructures\ImmutableArray;
use LMWF\ErrorHandling\ExceptionCode;
use LMWF\Http\Controller\IRoutedController;
use LMWF\Http\DataStructures\EntPageConf;
use LMWF\Http\DataStructures\StaticPageConf;
use LMWF\Repo\IRepo;
use LMWF\Http\DataStructures\IPageConf;
use UnexpectedValueException;

final readonly class RouteDefParser
{
    const bool DFT_IS_INDEXED = true;
    const bool DFT_IS_IN_HIERARCHY = true;
    const string FQCN_KN = 'fqcn';
    const string IS_IN_HIERARCHY_KN = 'inHierarchy';
    const string IS_INDEXED_KN = 'indexed';
    const string PAGE_CONTROLLER_FQCN_KN = 'fqcn';
    const string PAGE_ENT_REPO_FQCN_KN = 'repoFqcn';
    const string PAGE_KN = 'page';
    const string PAGE_TITLE_KN = 'title';
    const string PAGE_TYPE_ENT_VAL = 'ent';
    const string PAGE_TYPE_KN = 'type';
    const string PARAMS_KN = 'params';
    const string ROLES_KN = 'roles';
    const string ROUTES_KN = 'routes';
    const array ALL_KNS = [
        self::FQCN_KN,
        self::IS_IN_HIERARCHY_KN,
        self::IS_INDEXED_KN,
        self::PAGE_CONTROLLER_FQCN_KN,
        self::PAGE_ENT_REPO_FQCN_KN,
        self::PAGE_KN,
        self::PAGE_TITLE_KN,
        self::PAGE_TYPE_ENT_VAL,
        self::PAGE_TYPE_KN,
        self::PARAMS_KN,
        self::ROLES_KN,
        self::ROUTES_KN,
    ];

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
     * @param bool $allowOverridingParentRoles If true, a sub-route can add role its parent does not have.
     */
    public function parse(
        AppObject $route,
        ?array $parentRoles = null,
        bool $allowOverridingParentRoles = false,
        bool $isIndexed = self::DFT_IS_INDEXED,
        bool $isInHierarchy = self::DFT_IS_IN_HIERARCHY,
    ): RouteDef {
        // Check there are no unknown keys.
        foreach ($route as $key => $_) {
            if (!in_array($key, self::ALL_KNS, strict: true)) {
                throw new UnauthorizedAttributeConfException($key);
            }
        }


        $isIndexed = $route->getBoolOrNull(self::IS_INDEXED_KN) ?? $isIndexed;
        $isInHierarchy = $route->getBoolOrNull(self::IS_IN_HIERARCHY_KN) ?? $isInHierarchy;
        $pageConfs = $this->parsePageConf($route, $isIndexed, $isInHierarchy);

        $roles = null;
        if ($route->hasKey(self::ROLES_KN) || null === $parentRoles) {
            $roles = $route->getAppList(self::ROLES_KN)->toArray();
            if (!array_is_list($roles)) {
                // @todo Test, add code
                throw new UnexpectedValueException();
            }
            foreach ($roles as $role) {
                if (!is_string($role)) {
                    // @todo Test, add code
                    throw new UnexpectedValueException("Route definition adds a role which is not a valid string.");
                }
            }
            if (!$allowOverridingParentRoles && null !== $parentRoles) {
                foreach ($roles as $role) {
                    if (!in_array($role, $parentRoles, strict: true)) {
                        throw new SubRouteCannotAddRoleConfException($role);
                    }
                }
            }
        }

        // Set sub-route definitions.
        $children = [];
        if ($route->hasKey(self::ROUTES_KN)) {
            foreach ($route->getAppObject(self::ROUTES_KN) as $seg => $subRoute) {
                if (!$subRoute instanceof ImmutableArray) {
                    // @todo Test
                    throw new UnexpectedValueException('SubRoute configuration is expected to be an AppObject or an empty ImmutableArray.');
                } elseif (!$subRoute instanceof AppObject) {
                    if (0 !== $subRoute->count()) {
                        // @todo Test
                        throw new UnexpectedValueException('SubRoute configuration is expected to be an AppObject if not empty.');
                    }
                    $children[$seg] = new RouteDef();
                } else {
                    $children[$seg] = $this->parse(
                        $subRoute,
                        $roles ?? $parentRoles,
                        $allowOverridingParentRoles,
                        $isIndexed,
                        $isInHierarchy,
                    );
                }
            }
        }

        return new RouteDef(
            $pageConfs[0],
            array_slice($pageConfs, 1),
            $children,
            $roles ?? $parentRoles,
        );
    }

    /**
     * @template T of object
     * @param string $toParse
     * @param class-string<T> $baseFqcn
     * @return class-string<T>
     */
    private function parseFqcn(string $toParse, string $baseFqcn): string
    {
        $toParse = str_replace('.', '\\', $toParse);
        if (!class_exists($toParse) || !is_subclass_of($toParse, $baseFqcn)) {
            // @todo Test, add code.
            throw new UnexpectedValueException("The route definition defined a FQCN with value '$toParse' but it is either not a FQCN of an existing class, not a FQCN at all, or the FQCN of a class that does not implement $baseFqcn.");
        }
        return $toParse;
    }

    /**
     * @param AppObject<mixed> $routeDefData
     * @return list{null|StaticPageConf, ...<null|IPageConf>}
     */
    private function parsePageConf(AppObject $routeDefData, bool $isIndexed, bool $isInHierarchy): array
    {
        if (!$routeDefData->hasKey(self::PAGE_KN)) {
            return [null];
        }
        $pageDefConfs = $routeDefData->getAppList(self::PAGE_KN);

        // @todo Test, refactor, add code
        $pageDefs = $pageDefConfs->map(fn (mixed $conf) => null === $conf ? null : (!$conf instanceof AppObject ? throw new InvalidArgumentException() : ($conf->hasKey(self::PAGE_ENT_REPO_FQCN_KN) ?
            new EntPageConf(
                $conf->getFqcn(self::PAGE_ENT_REPO_FQCN_KN, IRepo::class, convertDotsToBackslashes: true),
                $conf->getString(self::PAGE_TITLE_KN),
                $this->parseFqcn($conf->getString(self::PAGE_CONTROLLER_FQCN_KN), IRoutedController::class),
                $this->baseUrl,
                $isIndexed,
                $isInHierarchy,
            ) :
            new StaticPageConf(
                $conf->getString(self::PAGE_TITLE_KN),
                $this->parseFqcn($conf->getString(self::PAGE_CONTROLLER_FQCN_KN), IRoutedController::class),
                $this->baseUrl,
                $isIndexed,
                $isInHierarchy,
            ))))->toArray();

        // @todo Test
        if (!$pageDefs[0] instanceof StaticPageConf && null !== $pageDefs[0]) {
            throw new UnexpectedValueException();
        }
        return $pageDefs;
    }
}
