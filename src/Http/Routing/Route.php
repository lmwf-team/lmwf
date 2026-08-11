<?php

declare(strict_types=1);

namespace LM\WebFramework\Http\Routing;

use InvalidArgumentException;

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
     * @param string[] $relevantSegs the associated path segments of the path
     * that instantiated the current route. For a parameterised route, only the
     * segments corresponding to the arguments are passed.
     * @todo PathSegList?
     */
    public function __construct(
        public readonly RouteDef $routeDef,
        public readonly array $relevantSegs,
        public readonly ?Route $parent = null,
        public readonly int $nArgs = 0,
    ) {
        if ($nArgs < 0) {
            throw new InvalidArgumentException("A Route's number of arguments cannot be negative, received {$nArgs}.");
        }
        if ($routeDef instanceof ParentRoute && $nArgs > 0) {
            throw new InvalidArgumentException("A instantiation of a ParentRoute cannot have arguments.");
        }
        if ($routeDef instanceof ParameterizedRoute) {
            if ($nArgs < $routeDef->minArgs) {
                throw new InvalidArgumentException("Instantiation of ParameterizedRoute has a number of arguments below the minimum ({$nArgs} < {$routeDef->minArgs}).");
            } elseif ($nArgs > $routeDef->maxArgs) {
                throw new InvalidArgumentException("Instantiation of ParameterizedRoute has a number of arguments above the maximum ({$nArgs} > {$routeDef->maxArgs}).");
            }
        }
        if (0 === count($relevantSegs)) {
            throw new InvalidArgumentException("A route must have relevant path segments.");
        }
        foreach ($relevantSegs as $seg) {
            if (!is_string($seg)) {
                throw new InvalidArgumentException("A path segment must be a string.");
            }
        }
    }

    public function getFqcn(): string
    {
        return $this->routeDef->fqcn;
    }

    public function getPath(): string
    {
        if (null === $this->parent) {
            return '/';
        } elseif ('/' === $this->parent->getPath()) {
            return '/' . implode('/', $this->relevantSegs);
        } else {
            return $this->parent->getPath() . '/' . implode('/', $this->relevantSegs);
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
