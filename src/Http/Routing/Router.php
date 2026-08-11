<?php

declare(strict_types=1);

namespace LM\WebFramework\Http\Routing;

use DomainException;
use LM\WebFramework\ErrorHandling\Log;
use LM\WebFramework\Http\Routing\Exception\RouteNotFoundException;
use LogicException;

final readonly class Router
{
    /**
     * Convert an ABSOLUTE path to a list of path segments, CONVERTS "" to "/".
     *
     * A "path segment" is defined in the context of lmwf as the
     * URL-decoded part of each path segment of the given absolute path.
     *
     * @param string $absPath An *ABSOLUTE*, valid HTTP path.
     * @return string[]
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
    public function getRouteFromPath(RouteDef $routeDef, string $path): Route
    {
        $segs = self::getSegs($path);
        Log::debug('Segments are: [' . implode(',', $segs) . ']');
        return $this->getRouteFromSegs($routeDef, null, $segs[0], array_slice($segs, 1));
    }

    /**
     * @todo Create SegsList type?
     */
    public function getRouteFromSegs(
        RouteDef $routeDef,
        ?Route $parentRoute,
        string $currentSeg,
        array $nextSegs,
    ): Route {
        Log::debug("Current seg is '{$currentSeg}', next segs are: [" . implode(', ', $nextSegs) . "].");


        $nArgs = count($nextSegs);
        if ($routeDef->nArgsLowerLimit > $nArgs) {
                throw new RouteNotFoundException("The requested route needs more arguments. (It received {$nArgs} when it should be at least {$routeDef->nArgsLowerLimit}.)");
        }
        $route = new Route($routeDef, $currentSeg, array_slice($nextSegs, 0, $routeDef->nArgsUpperLimit), $parentRoute);
        if ($routeDef->nArgsUpperLimit < $nArgs) {
            if (0 === count($routeDef->subroutes)) {
                throw new RouteNotFoundException("The requested route needs less arguments. (It received {$nArgs} when it should be at most {$routeDef->nArgsUpperLimit}.)");
            }
            Log::debug("Current route has subroutes.");
            $nextSeg = $nextSegs[$routeDef->nArgsUpperLimit];
            if (!key_exists($nextSeg, $routeDef->subroutes)) {
                throw new RouteNotFoundException("No child route could be found for segment: {$nextSeg}.");
            }

            return $this->getRouteFromSegs($routeDef->subroutes[$nextSeg], $route, $nextSeg, array_slice($nextSegs, $routeDef->nArgsUpperLimit + 1));
        }

        return $route;
    }
}
