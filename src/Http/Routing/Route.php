<?php

declare(strict_types=1);

namespace LMWF\Http\Routing;

use DomainException;
use InvalidArgumentException;
use LMWF\ErrorHandling\ExceptionCode;
use LMWF\ErrorHandling\UnexpectedValueTypeException;
use LMWF\Http\DataStructures\IPageConf;
use LMWF\Http\DataStructures\RouteDef;

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
        public ?Route $parent,
        public string $seg,
        public array $params = [],
    ) {
        if (!array_is_list($params)) {
            // @Todo Add code and message, test.
            throw new InvalidArgumentException();
        }
        // @todo Check params is a list
        foreach ($params as $i => $param) {
            // @todo Check it is actually a segment
            if (!is_string($param)) {
                // @todo Test exception is thrown
                throw new UnexpectedValueTypeException(
                    'string',
                    $param,
                    ExceptionCode::HTTP_ROUTING_ROUTE_PARAM_IS_NOT_STRING->value,
                    messageFmt: 'Route parameters are path segments, meaning they must be string, but param ' . $i . ' is %2$s.',
                );
            }
        }

        if (count($params) > $def->getNParamsMax()) {
            // @todo Test
            // @todo Code
            throw new InvalidArgumentException('Cannot instantiate a route with more parameters than its definition accepts.');
        }

        // If it is the root route.
        if (null === $this->parent) {
            if ('' !== $this->seg) {
                // @todo Also check that if parent, seg is the parent's seg.
                throw new DomainException(
                    "The root route can only match an empty path segment, but a Route was instantiated with a segment of '{$this->seg}'.",
                    ExceptionCode::HTTP_ROUTING_ROUTE_ROOT_ROUTE_HAS_NON_EMPTY_SEG->value,
                );
            } elseif (key_exists('', $this->def->children)) {
                // The root route cannot have a child with an empty seg. This
                // would conflict with our definition of a route definition
                // which is a partition (in the mathematical sense) of all the
                // paths.
                throw new InvalidArgumentException(
                    'The root route has a direct child with an empty seg.',
                    ExceptionCode::HTTP_ROUTING_ROUTE_ROOT_ROUTE_HAS_CHILD_WITH_EMPTY_SEG->value,
                );
            } elseif ([] !== $def->params) {
                throw new InvalidArgumentException(
                    "Route instantiated with a route definition that accepts up to {$def->getNParamsMax()} route parameters. However, the root route cannot accept parameters. This is because it could then match the path '/', which is ambiguous with the default path for the root route with no parameters.",
                    ExceptionCode::HTTP_ROUTING_ROUTE_ROOT_ROUTE_ACCEPTS_PARAMS->value,
                );
            }
        }
    }

    /**
     * @return positive-int
     */
    public function getNParams(): int
    {
        return count($this->params);
    }

    public function getPageConf(): ?IPageConf
    {
        $nParams = $this->getNParams();
        return 0 === $nParams ? $this->def->noParamConf : $this->def->params[$nParams - 1];
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

    /**
     * Return true if both routes share the same route definition tree.
     * 
     * This means that their definition is the same RouteDefinition INSTANCE,
     * that their parent, if defined, is equal by this method's standards, and
     * that other properties are strictly equal in terms of the data they
     * contain.
     */
    public function isEqual(Route $route): bool
    {
        if ($route === $this) {
            return true;
        } elseif ($route->def !== $this->def) {
            return false;
        } elseif ($route->seg !== $this->seg) {
            return false;
        } elseif ((null === $route->parent || null === $this->parent) && $route->parent !== $this->parent) {
            return false;
        } elseif (null !== $route->parent && !$route->parent->isEqual($this->parent)) {
            return false;
        } elseif (null === $route->parent && $route->parent !== $this->parent) {
            return false;
        } elseif ($route->params !== $this->params) {
            return false;
        }

        return true;
    }
}
