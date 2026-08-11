<?php

declare(strict_types=1);

namespace LM\WebFramework\Http\Routing;

use LM\WebFramework\Http\Routing\Exception\RouteNotFoundException;
use LogicException;

final readonly class Router
{
    /**
     * @param string $path An arbitrary string made of segments separated by one or more forward slashes.
     */
    public function getRouteFromPath(RouteDef $route, string $path): Route
    {
        $segs = array_values(array_filter(explode('/', $path), fn($value) => '' !== $value));
        return $this->getRouteFromSegs($route, null, $segs);
    }

    /**
     * @param string[] $segs
     * @param int $i The index of the next path segment.
     * @todo Create SegsList type?
     */
    public function getRouteFromSegs(RouteDef $routeDef, ?Route $parentRoute, array $segs, int $i = 0): Route
    {
        $nSegs = count($segs);

        $nArgs = $nSegs - $i;

        if ($routeDef instanceof ParameterizedRoute) {
            $relevantSegs = 0 === $i ? $segs : array_slice($segs, $i - 1, $nArgs + 1);
            if ($nArgs < $routeDef->minArgs || $nArgs > $routeDef->maxArgs) {
                throw new RouteNotFoundException("No route could be found for segment. It does not have the correct number of arguments. ({$nArgs} when it should be between {$routeDef->minArgs} and {$routeDef->maxArgs}.)");
            }
            return new Route($routeDef, $relevantSegs, $parentRoute, $nArgs);
        } elseif ($routeDef instanceof ParentRoute) {
            $relevantSegs = 0 === $i ? [] : [$segs[$i - 1]];

            if ($i === $nSegs) {
                return new Route($routeDef, $relevantSegs, $parentRoute);
            }
            $seg = $segs[$i];
            if (!key_exists($seg, $routeDef->routes)) {
                throw new RouteNotFoundException("No child route could be found for segment: {$segs[$i]}.");
            }
            $route = new Route($routeDef, $relevantSegs, $parentRoute);
            return $this->getRouteFromSegs($routeDef->routes[$seg], $route, $segs, $i + 1);
        } elseif ($routeDef instanceof OnlyChildParentRouteDef) {
            $relevantSegs = 0 === $i ? [] : [$segs[$i - 1]];
            $route = new Route($routeDef, $relevantSegs, $parentRoute);
            if ($i === $nSegs) {
                return $route;
            } else {
                return $this->getRouteFromSegs($routeDef->onlyChild, $route, array_slice($segs, $i), 0);
            }
        }
        throw new LogicException("A route definition can only be a ParentRoute or an Route.");
    }
}