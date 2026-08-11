<?php

declare(strict_types=1);

namespace LMWF\Http\Routing;

use DomainException;
use LMWF\Conf\Http\RouteDef;
use LMWF\ErrorHandling\Log;
use LMWF\Http\Controller\Issue\RouteNotFoundIssue;
use LMWF\Http\Controller\Issue\RoutingParamIssue;
use LMWF\Http\Controller\Issue\RoutingParamIssueCode;
use LMWF\Http\Routing\Exception\RouteNotFoundException;
use LogicException;

final readonly class Router
{
    /**
     * Convert an ABSOLUTE path to a list of path segments, CONVERTS "" to "/".
     *
     * A "path segment" is defined in the context of LMWF as the
     * URL-decoded part of each path segment of the given absolute path.
     *
     * @param string $absPath An *ABSOLUTE*, valid HTTP path.
     * @return list<string>
     */
    public function getSegs(string $absPath): array
    {
        if (0 !== strpos($absPath, '/')) {
            if ('' !== $absPath) {
                throw new DomainException('Passed path is not absolute.');
            }
            $absPath = '/';
        }

        return array_map(fn ($seg) => urldecode($seg), explode('/', $absPath));
    }

    /**
     * @param string $path An arbitrary string made of segments separated by one or more forward slashes.
     */
    public function getRouteFromPath(RouteDef $routeDef, string $path): Route|RoutingParamIssue|RouteNotFoundIssue
    {
        $segs = self::getSegs($path);
        Log::debug('Segments are: [' . implode(',', $segs) . ']');
        return $this->getRouteFromSegs($routeDef, null, $segs[0], array_slice($segs, 1));
    }

    /**
     * @todo Create SegsList type?
     * @param list<string> $nextSegs
     */
    public function getRouteFromSegs(
        RouteDef $routeDef,
        ?Route $parentRoute,
        string $currentSeg,
        array $nextSegs,
    ): Route|RoutingParamIssue|RouteNotFoundIssue {
        Log::debug("Current seg is '{$currentSeg}', next segs are: [" . implode(', ', $nextSegs) . "].");


        $nArgs = count($nextSegs);
        if ($routeDef->nArgsLowerLimit > $nArgs) {
            return new RoutingParamIssue(RoutingParamIssueCode::NotEnoughParams, $routeDef, $nArgs);
        }
        $route = new Route($routeDef, $currentSeg, array_slice($nextSegs, 0, $routeDef->nArgsUpperLimit), $parentRoute);
        if ($routeDef->nArgsUpperLimit < $nArgs) {
            if (0 === count($routeDef->subroutes)) {
                return new RoutingParamIssue(RoutingParamIssueCode::TooManyParams, $routeDef, $nArgs);
            }
            Log::debug("Current route has subroutes.");
            $nextSeg = $nextSegs[$routeDef->nArgsUpperLimit];
            if (!key_exists($nextSeg, $routeDef->subroutes)) {
                return new RouteNotFoundIssue($nextSeg);
            }

            return $this->getRouteFromSegs($routeDef->subroutes[$nextSeg], $route, $nextSeg, array_slice($nextSegs, $routeDef->nArgsUpperLimit + 1));
        }

        return $route;
    }
}
