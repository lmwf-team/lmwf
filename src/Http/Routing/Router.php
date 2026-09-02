<?php

declare(strict_types=1);

namespace LMWF\Http\Routing;

use DomainException;
use InvalidArgumentException;
use LMWF\ErrorHandling\ExceptionCode;
use LMWF\Http\DataStructures\RouteDef;
use LMWF\ErrorHandling\Log;
use LMWF\Http\Controller\Issue\RouteNotFoundIssue;
use LMWF\Http\Controller\Issue\RoutingParamIssue;
use LMWF\Http\Controller\Issue\RoutingParamIssueCode;

final readonly class Router
{
    /**
     * @param string $absPath An absolute, URL-decoded HTTP path. Must begin
     * with a forward slash, unless it is empty.
     */
    public function getRouteFromPath(RouteDef $rootRouteDef, string $absPath): Route|RoutingParamIssue|RouteNotFoundIssue
    {
        $segs = $this->splitPathInSegs($absPath);
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
     * @todo Always return a route. RoutingParamIssue and RouteNotFoundIssue
     * are routes themselves.
     */
    public function getRouteFromSegs(
        RouteDef $routeDef,
        ?Route $parentRoute,
        string $currentSeg,
        array $nextSegs,
    ): Route|RoutingParamIssue|RouteNotFoundIssue {
        Log::debug("Current seg is '{$currentSeg}', next segs are: [" . implode(', ', $nextSegs) . "].");

        if (!array_is_list($nextSegs)) {
            // @todo Test
            throw new InvalidArgumentException();
        }

        $nNextSegs = count($nextSegs);

        $nParamsTotal = min($nNextSegs, $routeDef->getNParamsMax());

        $mutRoute = null;
        for ($nParams = 0; $nParams <= $nParamsTotal; $nParams++) {
            $mutRoute = new Route(
                $routeDef,
                $mutRoute ?? $parentRoute,
                $currentSeg,
                array_slice($nextSegs, 0, $nParams),
            );
        }
        $route = $mutRoute;

        if ($nParamsTotal < $nNextSegs) {
            if (0 === count($routeDef->children)) {
                return new RoutingParamIssue(RoutingParamIssueCode::TooManyParams, $routeDef, $nNextSegs);
            }

            Log::debug("Current route has sub-route definitions.");

            $nextSeg = $nextSegs[$nParamsTotal];

            if (!key_exists($nextSeg, $routeDef->children)) {
                return new RouteNotFoundIssue($nextSeg);
            }

            return $this->getRouteFromSegs(
                $routeDef->children[$nextSeg],
                $route,
                $nextSeg, 
                array_slice($nextSegs, $nParamsTotal + 1),
            );
        }

        return $route;
    }

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
        throw new DomainException(
            'Passed path is not absolute.',
            ExceptionCode::HTTP_ROUTING_ROUTER_GIVEN_PATH_IS_NOT_ABSOLUTE->value,
        );
    }
}
