<?php

declare(strict_types=1);

namespace LMWF\Conf\Http;

use InvalidArgumentException;
use LMWF\Http\DataStructures\PageConf;
use LMWF\ErrorHandling\ExceptionCode;
use LMWF\ErrorHandling\UnexpectedValueTypeException;
use LMWF\Http\DataStructures\StaticPageConf;
use OutOfBoundsException;
use PHP_CodeSniffer\Util\ExitCode;

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
    // @todo Find a better name.
    public array $pageConfs;
    public array $subRouteDefs;
    public int $nParamsLeast;
    public int $nParamsMax;

    /**
     * @param array<string, self> $subRouteDefs The child routes as an array of route definitions, indexed by the path segment through which they are accessed.
     */
    public function __construct(
        array|null|StaticPageConf $pageConfs = [null],
        array $subRouteDefs = [],
        int $nParamsLeast = 0,
        int $nParamsMax = 0,
    ) {
        $this->pageConfs = is_array($pageConfs) ? $pageConfs : [$pageConfs];
        $this->subRouteDefs = $subRouteDefs;
        $this->nParamsLeast = $nParamsLeast;
        $this->nParamsMax = $nParamsMax;

        // @todo To move in PageConf
        // foreach ($roles as $role) {
        //     if (!is_string($role)) {
        //         throw new InvalidArgumentException(
        //             "A role must be a string (Found role equal to '$role'.)",
        //             ExceptionCode::CONF_HTTP_ROUTEDEF_ROLE_IS_NOT_A_STRING->value,
        //         );
        //     }
        // }

        if ($nParamsLeast < 0) {
            throw new InvalidArgumentException(
                "The minimum number of arguments for a route cannot be negative, received {$nParamsLeast}.",
                ExceptionCode::CONF_HTTP_ROUTEDEF_N_ARGS_LOWER_IS_NEG->value,
            );
        } elseif ($nParamsLeast > $nParamsMax) {
            throw new InvalidArgumentException(
                "The minimum number of arguments for a route (here {$nParamsLeast}) cannot be above its maximum number of arguments (here {$nParamsMax}).",
                ExceptionCode::CONF_HTTP_ROUTEDEF_N_ARGS_UPPER_IS_BELOW_LOWER_IS_NEG->value,
            );
        }

        // @todo Check type of value
        foreach ($this->pageConfs as $nOfParams => $paramConf) {
            if (!is_int($nOfParams)) {
                throw new UnexpectedValueTypeException(
                    'int',
                    $nOfParams,
                    ExceptionCode::CONF_HTTP_ROUTEDEF_PAGE_CONF_KEY_NOT_INT->value,
                );
            } elseif (!is_int($nOfParams) || $nOfParams < $nParamsLeast || $nOfParams > $nParamsMax) {
                throw new OutOfBoundsException(
                    "The 'by-param' configuration array has the invalid key '{$nOfParams}', it must be within the specified nParamsLeast ($nParamsLeast) and nParamsMax ($nParamsMax).",
                    ExceptionCode::CONF_HTTP_ROUTEDEF_PAGE_CONF_KEY_OUT_OF_BOUNDS->value,
                );
            }
        }
        if (!key_exists($nParamsLeast, $this->pageConfs)) {
            throw new InvalidArgumentException(
                "No configuration defined for the mininum number of parameters this route definition accepts ($nParamsLeast).",
                ExceptionCode::CONF_HTTP_ROUTEDEF_PAGE_CONF_MISSING->value,
            );
        }

        foreach ($subRouteDefs as $pathSegment => $routeDef) {
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
    }
}
