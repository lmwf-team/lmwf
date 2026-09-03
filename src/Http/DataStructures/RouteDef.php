<?php

declare(strict_types=1);

namespace LMWF\Http\DataStructures;

use InvalidArgumentException;
use LMWF\ErrorHandling\ExceptionCode;
use LMWF\Http\DataStructures\IPageConf;
use LMWF\Http\DataStructures\StaticPageConf;

/**
 * Associates a page configuration or null to a consecutive set of HTTP path
 * segments.
 *
 * It was necessary to introduce the dual concept of a route and of a route
 * definition. This is because some parts of the application are only concerned
 * with defining an exclusive set of URL paths (a route definition) in order to
 * provide informations about them, including how to respond to them.
 * Meanwhile, the actual route matches only specific path (a route) and is
 * instantiated at runtime necessarily.
 *
 * Unlike Route-s, a RouteDef only knows about its direct descendants not about
 * its parent.
 *
 * @todo Make it implement ArrayAccess to access sub-route definitions.
 */
final readonly class RouteDef
{
    /**
     * @param null|StaticPageConf $noParamConf The route configuration for
     * routes of this definition that did not receive any parameters. It's not
     * merged with $params for multiple reasons: makes counting actual parameters
     * easier (though the first segment could be considered a paraeter in its
     * own right), prevents empty $params which would be meaningless (a route
     * definition has to match at least one path on at least one segment),
     * makes it easier to impose a type on the first route configuration.
     * Defining a configuration for each number of parameters, including 0,
     * also allows us to remove the NotEnoughParams exception and the code
     * to check it is thrown.
     * @param list<null|IPageConf> $params Route configuration
     * for each index of the last received route parameter.
     * @param array<string, self> $children The child routes as an array of route definitions, indexed by the path segment through which they are accessed.
     * @param list<string> $roles Roles for this route definition and its descendants if not overwritten.
     */
    public function __construct(
        public null|IPageConf $noParamConf = null,
        public array $params = [],
        public array $children = [],
        public array $roles = [],
    ) {
        if (!array_is_list($params)) {
            throw new InvalidArgumentException(
                'The $params array must be a list associating a configuration for each parameter index',
                ExceptionCode::HTTP_DATASTRUCTURES_ROUTEDEF_PAGE_CONF_KEY_OUT_OF_BOUNDS->value,
            );
        }
        // @todo Check type of value
        foreach ($this->params as $iParam => $conf) {
            if (null !== $conf && !$conf instanceof IPageConf) {
                throw new InvalidArgumentException(
                    "Configuration for {$iParam} route parameters must be an array containing only IPageConf or null values.",
                    ExceptionCode::HTTP_DATASTRUCTURES_ROUTEDEF_PAGE_CONF_VALUE_INVALID_TYPE->value,
                );
            }
        }

        foreach ($children as $pathSegment => $routeDef) {
            if (!is_string($pathSegment)) {
                throw new InvalidArgumentException(
                    "Each route definition must be identified by one path segment. (Found path segment equal to '$pathSegment'.)",
                    ExceptionCode::HTTP_DATASTRUCTURES_ROUTEDEF_SUBROUTE_PATH_SEGMENT_IS_NOT_A_STRING->value,
                );
            }
            if (!$routeDef instanceof RouteDef) {
                throw new InvalidArgumentException(
                    "Routes must define a route definition. (Got a route definition of type " . (is_object($routeDef) ? $routeDef::class : gettype($routeDef)) . ".)",
                    ExceptionCode::HTTP_DATASTRUCTURES_ROUTEDEF_SUBROUTE_DEF_IS_NOT_A_ROUTEDEF->value,
                );
            }
        }

        foreach ($roles as $role) {
            if (!is_string($role)) {
                throw new InvalidArgumentException(
                    "A role must be a string (Found role equal to '$role'.)",
                    ExceptionCode::HTTP_DATASTRUCTURES_ROUTEDEF_ROLE_IS_NOT_A_STRING->value,
                );
            }
        }
    }

    /**
     * @return int<0, max>
     */
    public function getNParamsMax(): int
    {
        return count($this->params);
    }
}
