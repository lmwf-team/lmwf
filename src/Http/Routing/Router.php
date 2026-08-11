<?php

declare(strict_types=1);

namespace LM\WebFramework\Http\Routing;

use LM\WebFramework\Http\Routing\Exception\RouteNotFoundException;
use LogicException;

final readonly class Router
{
    /**
     * A Path Segment is defined as any part of the Request Target
     * (origin-form of the composed URI) that is between two slashes,
     * or the last part after the last slash.
     *
     * @param string $path An URL-encoded HTTP path, relative to the scheme,
     * host and port.
     * @todo Use pipe operator!
     * @return array<string>
     */
    public function getSegmentsFromAbsolutePath(string $path): array
    {
        if ('/' === $path) {
            // Normally, the empty path "" and absolute path "/" are considered
            // equal as defined in RFC 7230 Section 2.7.3.
            $path = '';
        }
        return array_map(fn ($seg) => urldecode($seg), explode('/', $path));
    }

    /**
     * @param string $path An arbitrary string made of segments separated by one or more forward slashes.
     */
    public function getRouteFromPath(RouteDef $route, string $path): Route
    {
        $segs = self::getSegmentsFromAbsolutePath($path);
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
        if ($routeDef instanceof ParameterizedRoute) {
            $nArgs = count($nextSegs);
            if ($nArgs < $routeDef->minArgs || $nArgs > $routeDef->maxArgs) {
                throw new RouteNotFoundException("No route could be found for segment. It does not have the correct number of arguments. ({$nArgs} when it should be between {$routeDef->minArgs} and {$routeDef->maxArgs}.)");
            }
            array_unshift($nextSegs, $currentSeg);
            return new Route($routeDef, $nextSegs, $parentRoute, $nArgs);
        } elseif ($routeDef instanceof ParentRoute) {
            if ([] === $nextSegs) {
                return new Route($routeDef, [$currentSeg], $parentRoute);
            }
            $seg = $nextSegs[0];
            if (!key_exists($seg, $routeDef->routes)) {
                throw new RouteNotFoundException("No child route could be found for segment: {$seg}.");
            }
            $route = new Route($routeDef, [$seg], $parentRoute);
            return $this->getRouteFromSegs($routeDef->routes[$seg], $route, $seg, array_slice($nextSegs, 1));
        } elseif ($routeDef instanceof OnlyChildParentRouteDef) {
            $relevantSegs = [$currentSeg];
            $route = new Route($routeDef, $relevantSegs, $parentRoute);
            if ([] === $nextSegs) {
                return $route;
            } else {
                return $this->getRouteFromSegs($routeDef->onlyChild, $route, $currentSeg, $nextSegs);
            }
        }
        throw new LogicException("Route type is not known.");
    }
}
