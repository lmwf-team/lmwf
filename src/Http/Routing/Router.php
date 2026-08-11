<?php

declare(strict_types=1);

namespace LM\WebFramework\Http\Routing;

use LM\WebFramework\Http\Error\RoutingError;
use LM\WebFramework\Http\Model\RouteInfo;
use LM\WebFramework\Http\Model\RouteInfoFactory;
use LM\WebFramework\Http\Routing\Exception\RouteNotFoundException;

final readonly class Router
{
    private RouteInfoFactory $mainRoute;

    public function __construct(
        private Route $rootRoute,
    ) {
    }

    /**
     * @param string $path An arbitrary string made of segments separated by one or more forward slashes.
     */
    public function getRouteFromUrl(string $path): ?Route
    {
        $segs = array_filter(explode('/', $path), fn($value) => '' !== $value) |> array_values(...);
        $nSegs = count($segs);

        $i = 0;
        $route = $this->rootRoute;
        if ($route instanceof ParameterizedRoute) {
            $nArgs = $nSegs - $i;
            if ($nArgs < $route->minArgs || $nArgs > $route->maxArgs) {
                throw new RouteNotFoundException("No route could be found for path: {$path}.");
            }
            return $route;
        }
        while ($i < $nSegs) {
            $seg = $segs[$i];
            if (!key_exists($seg, $route->routes)) {
                throw new RouteNotFoundException("No route could be found for path: {$path}.");
            }
            $route = $route->routes[$seg];
            $i++;
        }
        return $route;
    }

    /**
     * @param array<string> $pathSegments The path segments.
     * @return array Return the controller FQCN and the number of
     * parameters it takes.
     * @todo Create class for the returned object.
     * @todo Rename.
     * @todo If a route has subroutes, it must not have parameters? Of maybe it can, and it acts based on the order in which they are defined.
     */
    public function getRouteInfo(array $pathSegments): RouteInfo|RoutingError
    {
        $route = $this->mainRoute;
        $i = 0;
        
        while ($i < count($pathSegments)) {
            if (!$route->hasSubroutes()) {
                break;
            }
            $seg = $pathSegments[$i];
            if (!key_exists($seg, $route->routes)) {
                return RoutingError::RouteNotFound;
            }
            $route = $route->routes[$seg];
            $i++;
        }

        $nArgs = count($pathSegments) - $i;

        if (!key_exists($nArgs, $route->routes)) {
            return RoutingError::UnsupportedArgs;
        }

        return $route->routes[$nArgs];
    }
}