<?php

declare(strict_types=1);

namespace LMWF\Http\Routing;

use DomainException;
use LMWF\Conf\Http\RouteDef;
use LMWF\ErrorHandling\Log;
use LMWF\Http\Controller\Issue\RouteNotFoundIssue;
use LMWF\Http\Controller\Issue\RoutingParamIssue;
use LMWF\Http\Controller\Issue\RoutingParamIssueCode;

final readonly class Router
{
    /**
     * Convert an ABSOLUTE path to a list of path segments, CONVERTS "/" to "".
     *
     * A "path segment" is defined in the context of LMWF as the
     * URL-decoded part of each path segment of the given absolute path. It must
     * begin with a slash, except for the empty string. If it only contains a
     * slash, it is converted into an empty string.
     *
     * @param string $absPath An *ABSOLUTE*, valid HTTP path.
     * @return list<string>
     */
    public function splitPathInSegs(string $absPath): array
    {
        if ('/' === $absPath || '' === $absPath) {
            return [''];
        } elseif (0 === strpos($absPath, '/')) {
            // If the path begins with a slash and is not simply "/", split it
            // by slash. This is guaranteed to have a first, empty element ("").
            return array_map(fn ($seg) => urldecode($seg), explode('/', $absPath));
        }
        // $absPath does not begin with a slash AND is not empty.
        throw new DomainException('Passed path is not absolute.');
    }

    /**
     * @param string $absPath An absolute HTTP path. Must begin with a forward slash, unless it is empty.
     */
    public function getRouteFromPath(RouteDef $rootRouteDef, string $absPath): Route|RoutingParamIssue|RouteNotFoundIssue
    {
        $segs = self::splitPathInSegs($absPath);
        Log::debug('Segments are: [' . implode(',', $segs) . ']');

        return $this->getRouteFromSegs(
            $rootRouteDef, 
            parentRoute: null,
            currentSeg: $segs[0],
            nextSegs: array_slice($segs, 1),
        );
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

        $nNextSegs = count($nextSegs);

        if ($routeDef->nParamsLeast > $nNextSegs) {
            return new RoutingParamIssue(RoutingParamIssueCode::NotEnoughParams, $routeDef, $nNextSegs);
        }

        $nParamsTotal = min($nNextSegs, $routeDef->nParamsMax);

        $mutRoute = null;
        for ($nParams = $routeDef->nParamsLeast; $nParams <= $nParamsTotal; $nParams++) {
            $mutRoute = new Route(
                $routeDef,
                $currentSeg,
                $mutRoute ?? $parentRoute,
                array_slice($nextSegs, 0, $nParams),
            );
        }
        $route = $mutRoute;

        if ($nParamsTotal < $nNextSegs) {
            if (0 === count($routeDef->subRouteDefs)) {
                return new RoutingParamIssue(RoutingParamIssueCode::TooManyParams, $routeDef, $nNextSegs);
            }

            Log::debug("Current route has sub-route definitions.");

            $nextSeg = $nextSegs[$nParamsTotal];

            if (!key_exists($nextSeg, $routeDef->subRouteDefs)) {
                return new RouteNotFoundIssue($nextSeg);
            }

            return $this->getRouteFromSegs(
                $routeDef->subRouteDefs[$nextSeg],
                $route,
                $nextSeg, 
                array_slice($nextSegs, $nParamsTotal + 1),
            );
        }

        return $route;
    }
}
