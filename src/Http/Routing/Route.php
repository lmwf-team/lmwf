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
 * The root route is the parent of all routes in the context of any request. It
 * sets shared roles, but cannot be associated with a controller.
 * As the path of any request starts with '/' (even '' as it is equivalent to
 * '/'), and as a path segment is defined as each URL-decoded segment of the
 * absolute path split by (before being decoded) forward slash, then the first
 * path segment of any request is '', which matches the root route.
 * The home route is the root route's child (assuming it is a parent route) with
 * the key '', assuming it is defined.
 */
final readonly class Route
{
    /**
     * @param array<string, RouteDef>
     */
    public static function createRootRoute(array $routes): self
    {
        $rootRouteDef = new RouteDef(null, conf: new ParentRouteConf($routes));
        return new self($rootRouteDef, '');
    }

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
            throw new DomainException("A parent route cannot have parameters.");
        }
        if ($routeDef->conf instanceof ParamRouteConf) {
            if ($nArgs < $routeDef->conf->nArgsLowerLimit) {
                throw new DomainException("Instantiation of a parameterized route has a number of arguments below the minimum ({$nArgs} < {$routeDef->conf->nArgsLowerLimit}).");
            } elseif ($nArgs > $routeDef->conf->nArgsUpperLimit) {
                throw new DomainException("Instantiation of parameterized route has a number of arguments above the maximum ({$nArgs} > {$routeDef->conf->nArgsUpperLimit}).");
            }
        }

        foreach ($parameters as $seg) {
            if (!is_string($seg)) {
                throw new InvalidArgumentException("A path segment must be a string.");
            }
        }

        if (null === $this->parent) {
            if ('' !== $this->seg) {
                throw new DomainException('The root route can only match an empty path segment.');
            } elseif (null !== $routeDef->fqcn) {
                throw new DomainException('The root route cannot be associated with a controller, unless it is a controller for when it receives parameters.');
            } elseif ($routeDef->conf instanceof ParamRouteConf && 0 === $routeDef->conf->nArgsLowerLimit) {
                throw new DomainException('The root route cannot accept a null number of parameters.');
            }
        }
    }

    /**
     * The FQCN of the controller associated with this route.
     */
    public function getFqcn(): ?string
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
     * This will always have a leading slash.
     */
    public function getPath(): string
    {
        $path = '';
        if (null !== $this->parent) {
            $path .= "{$this->parent->getPath()}/{$this->seg}";
        }
        if (count($this->parameters) > 0) {
            $path .= '/' . implode('/', $this->parameters);
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
