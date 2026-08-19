<?php

declare(strict_types=1);

namespace LMWF\Conf\Http;

use InvalidArgumentException;
use LMWF\Http\DataStructures\PageConf;
use LMWF\ErrorHandling\ExceptionCode;

/**
 * A route definition.
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
     * @param ?class-string<\LMWF\Http\Controller\IRoutedController> $fqcn The FQCN of the controller responsible for this
     * particular partition of paths. If null, this route definition only serves
     * to set the paths of sub route definitions, set shared roles, etc.
     * @param ($fqcn is string ? PageConf : $fqcnIfParams is string ? PageConf : null) $pageParam Parameters for the page, required if the route def has an associated controller ($fqcn or $fqcnIfParams).
     * @param list<string> $roles Required roles to access this route.
     * @param array<string, self> $subroutes The child routes as an array of route definitions, indexed by the path segment through which they are accessed.
     * @param ?class-string<\LMWF\Http\Controller\IRoutedController> $fqcnIfParams The controller if the route has parameters.
     * @todo What happens when an object argument has a default???
     */
    public function __construct(
        public ?string $fqcn,
        public ?PageConf $pageParam,
        public array $roles = [],
        public array $subroutes = [],
        public int $nArgsLowerLimit = 0,
        public int $nArgsUpperLimit = 0,
        public ?string $fqcnIfParams = null,
    ) {
        if (null === $fqcn && null === $fqcnIfParams) {
            if (null !== $pageParam) {
                throw new InvalidArgumentException(
                    'PageConf must be null if the route does not specify any associated controller.',
                    ExceptionCode::CONF_HTTP_ROUTEDEF_PAGEPARAM_IS_NOT_NULL->value,
                );
            }
        } else {
            if (null === $pageParam) {
                throw new InvalidArgumentException(
                    "PageConf must NOT be NULL if the route specifies at least one controller (\$fqcn is '$fqcn' and \$fqcnIfParams is '$fqcnIfParams'.",
                    ExceptionCode::CONF_HTTP_ROUTEDEF_PAGEPARAM_IS_NULL->value,
                );
            }
        }
        foreach ($roles as $role) {
            if (!is_string($role)) {
                throw new InvalidArgumentException(
                    "A role must be a string (Found role equal to '$role'.)",
                    ExceptionCode::CONF_HTTP_ROUTEDEF_ROLE_IS_NOT_A_STRING->value,
                );
            }
        }

        foreach ($subroutes as $pathSegment => $routeDef) {
            if (!is_string($pathSegment)) {
                throw new InvalidArgumentException(
                    "Each route definition must be identified by one path segment. (Found path segment equal to '$pathSegment'.)",
                    ExceptionCode::CONF_HTTP_ROUTEDEF_SUBROUTE_PATH_SEGMENT_IS_NOT_A_STRING->value,
                );
            }
            if (!$routeDef instanceof RouteDef) {
                throw new InvalidArgumentException(
                    "Routes must define a route definition. (Got a route definition of type " . (is_object($routeDef) ? $routeDef::class : gettype($routeDef)) . ".)",
                    ExceptionCode::CONF_HTTP_ROUTEDEF_SUBROUTE_DEF_IS_NOT_A_ROUTEDEF->value,
                );
            }
        }

        if ($nArgsLowerLimit < 0) {
            throw new InvalidArgumentException(
                "The minimum number of arguments for a route cannot be negative, received {$nArgsLowerLimit}.",
                ExceptionCode::CONF_HTTP_ROUTEDEF_N_ARGS_LOWER_IS_NEG->value,
            );
        } elseif ($nArgsLowerLimit > $nArgsUpperLimit) {
            throw new InvalidArgumentException(
                "The minimum number of arguments for a route (here {$nArgsLowerLimit}) cannot be above its maximum number of arguments (here {$nArgsUpperLimit}).",
                ExceptionCode::CONF_HTTP_ROUTEDEF_N_ARGS_UPPER_IS_BELOW_LOWER_IS_NEG->value,
            );
        }
    }
}
