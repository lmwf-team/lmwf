<?php

declare(strict_types=1);

namespace LM\WebFramework\Http\Routing;

use DomainException;
use InvalidArgumentException;
use LM\WebFramework\Http\Routing\Exception\RootRouteWithDefaultControllerException;
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
     * @param RouteDef $def The associated route definition.
     * @param string[] $parameters the associated path segments of the path
     * that instantiated the current route. For a parameterised route, only the
     * segments corresponding to the arguments are passed.
     * @todo PathSegList?
     */
    public function __construct(
        public RouteDef $def,
        public string $seg,
        public array $parameters = [],
        public ?Route $parent = null,
    ) {
        $nArgs = count($parameters);
        if ($def->conf instanceof ParentRouteConf && $nArgs > 0) {
            throw new DomainException("A parent route cannot have parameters.");
        }
        if ($def->conf instanceof ParamRouteConf) {
            if ($nArgs < $def->conf->nArgsLowerLimit) {
                throw new DomainException("Instantiation of a parameterized route has a number of arguments below the minimum ({$nArgs} < {$def->conf->nArgsLowerLimit}).");
            } elseif ($nArgs > $def->conf->nArgsUpperLimit) {
                throw new DomainException("Instantiation of parameterized route has a number of arguments above the maximum ({$nArgs} > {$def->conf->nArgsUpperLimit}).");
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
            } elseif (null !== $def->fqcn) {
                throw new RootRouteWithDefaultControllerException();
            } elseif ($def->conf instanceof ParamRouteConf && 0 === $def->conf->nArgsLowerLimit) {
                throw new DomainException('The root route cannot accept a null number of parameters.');
            }
        }
    }

    /**
     * The FQCN of the controller associated with this route.
     */
    public function getFqcn(): ?string
    {
        if ($this->def->conf instanceof ParamRouteConf && null !== $this->def->conf->fqcnIfParams) {
            return $this->def->conf->fqcnIfParams;
        } else {
            return $this->def->fqcn;
        }
    }

    /**
     * @return ?string the parameter from the given array at the given index, or null if
     * the index is beyond the array's range.
     */
    public function getParamOrNull(int $index): ?string
    {
        if ($index >= count($this->parameters)) {
            return null;
        }
        return $this->parameters[$index];
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
        return $this->def->roles;
    }
}
