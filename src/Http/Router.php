<?php

declare(strict_types=1);

namespace LM\WebFramework\Http;

use LM\WebFramework\Configuration\Configuration;
use LM\WebFramework\Http\Model\RouteInfo;
use LM\WebFramework\Http\Model\RouteInfoFactory;

final class Router
{
    private readonly RouteInfoFactory $mainRoute;

    public function __construct(Configuration $conf) {
        $this->mainRoute = new RouteInfoFactory($conf->getRoutes());
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

enum RoutingError {
    case RouteNotFound;
    case UnsupportedArgs;
};