<?php

declare(strict_types=1);

namespace LM\WebFramework\Http\Routing;

use LM\WebFramework\Http\Routing\Exception\RouteNotFoundException;
use LogicException;

final readonly class Router
{
    public function __construct(
        private ParameterizedRoute|ParentRoute $rootRoute,
    ) {
    }

    /**
     * @param string $path An arbitrary string made of segments separated by one or more forward slashes.
     */
    public function getRouteFromPath(string $path): Route
    {
        $segs = array_values(array_filter(explode('/', $path), fn($value) => '' !== $value));
        $nSegs = count($segs);

        $i = 0;
        $route = $this->rootRoute;
        if ($route instanceof ParameterizedRoute) {
            $nArgs = $nSegs - $i;
            if ($nArgs < $route->minArgs || $nArgs > $route->maxArgs) {
                throw new RouteNotFoundException("No route could be found for path: {$path}.");
            }
            return new Route($route, $nArgs);
        } elseif ($route instanceof ParentRoute) {
            while ($i < $nSegs) {
                $seg = $segs[$i];
                if (!key_exists($seg, $route->routes)) {
                    throw new RouteNotFoundException("No route could be found for path: {$path}.");
                }
                $route = $route->routes[$seg];
                $i++;
            }
            return new Route($route);
        }
        throw new LogicException("A route definition can only be a ParentRoute or an Route.");
    }
}