<?php

declare(strict_types=1);

namespace LM\WebFramework\Http\Routing;

use DomainException;
use LM\WebFramework\ErrorHandling\Log;
use LM\WebFramework\Http\Routing\Exception\RouteNotFoundException;
use LM\WebFramework\Http\Routing\RouteConf\ParamRouteConf;
use LM\WebFramework\Http\Routing\RouteConf\ParentRouteConf;
use LogicException;

final readonly class Router
{
    /**
     * Convert an ABSOLUTE path to a list of path segments, CONVERTS "" to "/".
     * 
     * A "path segment" is defined in the context of lmwf as the
     * URL-decoded part of each path segment of the given absolute path.
     *
     * @param string $path An *ABSOLUTE*, valid HTTP path.
     * @todo Use pipe operator!
     * @return array<string>
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
     * @param string[] $nextSegs
     * @param int $i The index of the next path segment.
     * @todo Create SegsList type?
     */
    public function getRouteFromSegs(
        RouteDef $routeDef,
        ?Route $parentRoute,
        string $currentSeg,
        array $nextSegs,
    ): Route {
        Log::debug("Current seg is '{$currentSeg}', next segs are: [" . implode(', ', $nextSegs) . "].");

        if ($routeDef->conf instanceof ParamRouteConf) {
            $nArgs = count($nextSegs);
            if ($nArgs < $routeDef->conf->nArgsLowerLimit || $nArgs > $routeDef->conf->nArgsUpperLimit) {
                throw new RouteNotFoundException("No route could be found for segment. It does not have the correct number of arguments. ({$nArgs} when it should be between {$routeDef->conf->nArgsLowerLimit} and {$routeDef->conf->nArgsUpperLimit}.)");
            }
            Log::debug("Found route with FQCN {$routeDef->fqcn}.");
            return new Route($routeDef, $currentSeg, $nextSegs, $parentRoute);
        } elseif ($routeDef->conf instanceof ParentRouteConf) {
            $route = new Route($routeDef, $currentSeg, [], $parentRoute);
            Log::debug("Current route definition is a parent route.");
            if ([] === $nextSegs) {
                return $route;
            }
            $nextSeg = $nextSegs[0];
            if (!key_exists($nextSeg, $routeDef->conf->routes)) {
                throw new RouteNotFoundException("No child route could be found for segment: {$nextSeg}.");
            }
            return $this->getRouteFromSegs($routeDef->conf->routes[$nextSeg], $route, $nextSeg, array_slice($nextSegs, 1));
        }
        throw new LogicException("Route type is not known.");
    }
}
