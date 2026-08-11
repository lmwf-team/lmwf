<?php

declare(strict_types=1);

namespace LM\WebFramework\Http\Routing;

use DomainException;
use LM\WebFramework\ErrorHandling\Log;
use LM\WebFramework\Http\Routing\Exception\RouteNotFoundException;
use LM\WebFramework\Http\Routing\RouteParam\ParameterizedRouteParam;
use LM\WebFramework\Http\Routing\RouteParam\ParentRouteParam;
use LogicException;

final readonly class Router
{
    /**
     * Convert an absolute path to a list of path segments.
     * 
     * A Path Segment is defined as the URL-decoded part of the path that is
     * delimited by forward slash and that does not contain, before being
     * decoded, any forward slash.
     * However, if the absolute path is "/", the only path segment is "".
     *
     * @param string $path An absolute, valid URL-encoded HTTP path (as can be
     * returned by PSR-7's getPath method) relative to the scheme, host and
     * port.
     * @todo Use pipe operator!
     * @return array<string>
     */
    public function getSegs(string $absPath): array
    {
        if (0 === strpos($absPath, '/')) {
            if (1 === strlen($absPath) || 1 === strpos($absPath, '/', 1)) {
                $absPath = substr($absPath, 1);
            }
        } elseif ('' !== $absPath) {
            throw new DomainException('Passed path is not absolute.');
        }

        return array_map(fn ($seg) => urldecode($seg), explode('/', $absPath));
    }

    /**
     * @param string $path An arbitrary string made of segments separated by one or more forward slashes.
     */
    public function getRouteFromPath(RouteDef $route, string $path): Route
    {
        $segs = self::getSegs($path);
        return $this->getRouteFromSegs($route, null, $segs[0], array_slice($segs, 1));
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

        if ($routeDef->params instanceof ParameterizedRouteParam) {
            $nArgs = count($nextSegs);
            if ($nArgs < $routeDef->params->nArgsLowerLimit || $nArgs > $routeDef->params->nArgsUpperLimit) {
                throw new RouteNotFoundException("No route could be found for segment. It does not have the correct number of arguments. ({$nArgs} when it should be between {$routeDef->params->nArgsLowerLimit} and {$routeDef->params->nArgsUpperLimit}.)");
            }
            return new Route($routeDef, $currentSeg, $nextSegs, $parentRoute);
        } elseif ($routeDef->params instanceof ParentRouteParam) {
            $route = new Route($routeDef, $currentSeg, $nextSegs, $parentRoute);
            if ([] === $nextSegs) {
                return $route;
            }
            $nextSeg = $nextSegs[0];
            if (!key_exists($nextSeg, $routeDef->routes)) {
                throw new RouteNotFoundException("No child route could be found for segment: {$nextSeg}.");
            }
            return $this->getRouteFromSegs($routeDef->params->routes[$nextSeg], $route, $nextSeg, array_slice($nextSegs, 1));
        }
        throw new LogicException("Route type is not known.");
    }
}
