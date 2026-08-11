<?php

declare(strict_types=1);

namespace LM\WebFramework\Http\Routing;

use InvalidArgumentException;
use DomainException;
use LM\WebFramework\Http\Routing\RouteConf\ParamRouteConf;
use LM\WebFramework\Http\Routing\RouteConf\ParentRouteConf;

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
     * @param string[] $parameters the associated path segments of the path
     * that instantiated the current route. For a parameterised route, only the
     * segments corresponding to the arguments are passed.
     * @todo PathSegList?
     */
    public function __construct(
        public RouteDef $routeDef,
        public string $seg,
        public array $parameters = [],
        public ?Route $parent = null,
    ) {
        $nArgs = count($parameters);
        if ($routeDef->conf instanceof ParentRouteConf && $nArgs > 0) {
            throw new InvalidArgumentException("A route with child routes cannot have parameters.");
        }
        if ($routeDef->conf instanceof ParamRouteConf) {
            if ($nArgs < $routeDef->conf->nArgsLowerLimit) {
                throw new InvalidArgumentException("Instantiation of a parameterized route has a number of arguments below the minimum ({$nArgs} < {$routeDef->conf->nArgsLowerLimit}).");
            } elseif ($nArgs > $routeDef->conf->nArgsUpperLimit) {
                throw new InvalidArgumentException("Instantiation of parameterized route has a number of arguments above the maximum ({$nArgs} > {$routeDef->conf->nArgsUpperLimit}).");
            }
        }

        foreach ($parameters as $seg) {
            if (!is_string($seg)) {
                throw new InvalidArgumentException("A path segment must be a string.");
            }
        }

        if (null === $this->parent && '' !== $this->seg) {
            throw new DomainException('A root route can only match an empty path segment.');
        }
    }

    /**
     * The FQCN of the controller associated with this route.
     */
    public function getFqcn(): string
    {
        if ($this->routeDef->conf instanceof ParamRouteConf && null !== $this->routeDef->conf->fqcnIfParams) {
            return $this->routeDef->conf->fqcnIfParams;
        } else {
            return $this->routeDef->fqcn;
        }
    }

    /**
     * Compute the absolute path from the root route up to this route.
     * 
     * This will always have a leading slash and no trailing slash.
     */
    public function getPath(): string
    {
        $path = $this->parent?->getPath() ?? '';
        $path .= '/';
        $path .= $this->seg;
        if (count($this->parameters) > 0) {
            $path .= '/';
            $path .= implode('/', $this->parameters);
        }
        return $path;
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return $this->routeDef->roles;
    }
}
