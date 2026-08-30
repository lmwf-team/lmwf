<?php

declare(strict_types=1);

namespace LMWF\Http\Routing;

use DomainException;
use InvalidArgumentException;
use LMWF\Conf\Http\RouteDef;
use LMWF\ErrorHandling\ExceptionCode;
use LMWF\Http\Routing\Exception\RootRouteWithDefaultControllerException;

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
 *
 * Unlike RouteDef-s, a Route only knows about its direct parent, and
 * not about its child routes.
 */
final readonly class Route
{
    /**
     * @param RouteDef $def The associated route definition.
     * @param list<string> $params the associated path segments of the path
     * that instantiated the current route. For a parameterised route, only the
     * segments corresponding to the arguments are passed.
     * @todo PathSegList?
     */
    public function __construct(
        public RouteDef $def,
        public string $seg,
        public ?Route $parent,
        public array $params = [],
    ) {
        $nArgs = count($params);

        if ($nArgs < $def->nParamsLeast) {
            throw new DomainException("Instantiation of a route has a number of arguments below the minimum in the route definition ({$nArgs} < {$def->nParamsLeast}).");
        } elseif ($nArgs > $def->nParamsMax) {
            throw new DomainException("Instantiation of a route has a number of arguments above the maximum in the route definition ({$nArgs} > {$def->nParamsMax}).");
        }

        foreach ($params as $param) {
            if (!is_string($param)) {
                throw new InvalidArgumentException("A path segment must be a string.");
            }
        }

        // If it is the root route.
        if (null === $this->parent) {
            if ('' !== $this->seg) {
                throw new DomainException(
                    'The root route can only match an empty path segment.',
                    ExceptionCode::HTTP_ROUTING_ROUTE_ROOT_ROUTE_HAS_NON_EMPTY_SEG->value,
                );
            } elseif (key_exists('', $this->def->subRouteDefs)) {
                // The root route cannot have a child with an empty seg. This
                // would conflict with our definition of a route definition
                // which is a partition (in the mathematical sense) of all the
                // paths.
                throw new InvalidArgumentException(
                    'The root route has a direct child with an empty seg.',
                    ExceptionCode::HTTP_ROUTING_ROUTE_ROOT_ROUTE_HAS_CHILD_WITH_EMPTY_SEG->value,
                );
            } elseif ($def->nParamsMax > 0) {
                throw new InvalidArgumentException(
                    "The root route cannot accept parameters, but received a route definition that says max is {$def->nParamsMax}. This is because it could match the path '/', which is ambiguous with the default path for the root route with no parameters.",
                    ExceptionCode::HTTP_ROUTING_ROUTE_ROOT_ROUTE_ACCEPTS_PARAMS->value,
                );
            }
        }
    }

    /**
     * The FQCN of the controller associated with this route.
     *
     * @return null|class-string<\LMWF\Http\Controller\IRoutedController>
     */
    public function getFqcn(): ?string
    {
        if (null !== $this->def->fqcnIfParams && count($this->params) > 0) {
            return $this->def->fqcnIfParams;
        }
        return $this->def->fqcn;
    }

    /**
     * @return ?string the parameter from the given array at the given index, or null if
     * the index is beyond the array's range.
     */
    public function getParamOrNull(int $index): ?string
    {
        if ($index >= count($this->params)) {
            return null;
        }
        return $this->params[$index];
    }

    /**
     * Compute the absolute path of the route.
     * 
     * If the route is the root route, an empty path is returned. An empty
     * slash should never be returned as the root route cannot accept parameters
     * and cannot have a child with an empty seg.
     */
    public function getPath(): string
    {
        if (null === $this->parent) {
            // The root route does not take parameters, and its seg is empty.
            return '';
        }

        if ([] !== $this->params) {
            // This route's parent is the same route definition with one less
            // parameters.
            return $this->parent->getPath() . '/' . $this->params[count($this->params) - 1];
        }

        // Route is not route and does not have parameters.
        return $this->parent->getPath() . '/' . $this->seg;
    }

    /**
     * @return list<string>
     */
    public function getRoles(): array
    {
        return $this->def->roles;
    }
}
