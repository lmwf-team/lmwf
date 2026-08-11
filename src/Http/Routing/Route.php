<?php

declare(strict_types=1);

namespace LM\WebFramework\Http\Routing;

use InvalidArgumentException;

/**
 * @todo Add name to a Route? Not great because name is often dynamic.
 * @todo Embed request?
 * @todo Should include query data.
 */
final readonly class Route
{
    /**
     * @param string[] $roles
     * @param array<string, self> $routes
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
        return  '/' . implode('/', $this->relevantSegs);
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return $this->routeDef->roles;
    }
}
