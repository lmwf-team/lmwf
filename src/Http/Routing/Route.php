<?php

declare(strict_types=1);

namespace LM\WebFramework\Http\Routing;

use InvalidArgumentException;
use LM\WebFramework\Http\Routing\RouteParam\ParameterizedRouteParam;
use LM\WebFramework\Http\Routing\RouteParam\ParentRouteParam;

/**
 * Instantiation of a RouteDef, based on a given path.
 *
 * A path (in the HTTP sense) is split into segments. Starting from the left,
 * one or more segments are associated with a route definition, and for those
 * segments, referred to as the "relevant segments", a route is instantiated.
 *
 * @todo Add name to a Route? Not great because name is often dynamic.
 * @todo Embed request? Probably best to only rely on path.
 * @todo Should include query data. Probably best to only rely on path.
 */
final readonly class Route
{
    /**
     * @param RouteDef $routeDef The associated route definition.
     * @param string[] $remainingSegs the associated path segments of the path
     * that instantiated the current route. For a parameterised route, only the
     * segments corresponding to the arguments are passed.
     * @todo PathSegList?
     */
    public function __construct(
        public RouteDef $routeDef,
        public string $seg,
        public array $remainingSegs,
        public ?Route $parent = null,
    ) {
        $nArgs = count($remainingSegs);
        if ($routeDef->params instanceof ParentRouteParam && $nArgs > 0) {
            throw new InvalidArgumentException("A route with child routes cannot have parameters.");
        }
        if ($routeDef->params instanceof ParameterizedRouteParam) {
            if ($nArgs < $routeDef->params->nArgsLowerLimit) {
                throw new InvalidArgumentException("Instantiation of a parameterized route has a number of arguments below the minimum ({$nArgs} < {$routeDef->params->nArgsLowerLimit}).");
            } elseif ($nArgs > $routeDef->params->nArgsUpperLimit) {
                throw new InvalidArgumentException("Instantiation of parameterized route has a number of arguments above the maximum ({$nArgs} > {$routeDef->params->nArgsUpperLimit}).");
            }
        }

        foreach ($remainingSegs as $seg) {
            if (!is_string($seg)) {
                throw new InvalidArgumentException("A path segment must be a string.");
            }
        }
    }

    /**
     * The FQCN of the controller associated with this route.
     */
    public function getFqcn(): string
    {
        return $this->routeDef->fqcn;
    }

    /**
     * Compute the full path from the root route up to this route.
     * 
     * This will always have a leading slash and no trailing slash.
     */
    public function getPath(): string
    {
        if (null === $this->parent) {
            return '/';
        } elseif ('/' === $this->parent->getPath()) {
            return '/' . implode('/', $this->remainingSegs);
        } else {
            return $this->parent->getPath() . '/' . implode('/', $this->remainingSegs);
        }
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return $this->routeDef->roles;
    }
}
