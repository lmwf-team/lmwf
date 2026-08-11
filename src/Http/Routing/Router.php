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
    public function getRouteFromPath(ParameterizedRoute|ParentRoute $route, string $path): Route
    {
        $segs = array_values(array_filter(explode('/', $path), fn($value) => '' !== $value));
        return $this->getRouteFromSegs($route, $segs);
    }

    /**
     * @param string[] $segs
     * @param int $i The index of the next path segment.
     * @todo Create SegsList type?
     */
    public function getRouteFromSegs(ParameterizedRoute|ParentRoute $route, array $segs, int $i = 0): Route
    {
        $nSegs = count($segs);

        $nArgs = $nSegs - $i;
        if ($route instanceof ParameterizedRoute) {
            if ($nArgs < $route->minArgs || $nArgs > $route->maxArgs) {
                throw new RouteNotFoundException("No route could be found for segment. It does not have the correct number of arguments. ({$nArgs} when it should be between {$route->minArgs} and {$route->maxArgs}.)");
            }
            return new Route($route, $nArgs);
        } elseif ($route instanceof ParentRoute) {
            if ($i === $nSegs) {
                return new Route($route);
            }
            $seg = $segs[$i];
            if (!key_exists($seg, $route->routes)) {
                throw new RouteNotFoundException("No child route could be found for segment: {$segs[$i]}.");
            }
            return $this->getRouteFromSegs($route->routes[$seg], $segs, $i + 1);
        }
        throw new LogicException("A route definition can only be a ParentRoute or an Route.");
    }
}