<?php

declare(strict_types=1);

namespace LM\WebFramework\Http\Routing;

use InvalidArgumentException;

final readonly class Route
{
    /**
     * @param string[] $roles
     * @param array<string, self> $routes
     */
    public function __construct(
        public ParameterizedRoute|ParentRoute $routeDef,
        public int $nArgs = 0,
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
                throw new InvalidArgumentException("Instantiation of ParameterizedRoute has a number of arguments abvoe the maximum ({$nArgs} > {$routeDef->maxArgs}).");
            }
        }
    }

    public function getFqcn(): string
    {
        return $this->routeDef->fqcn;
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return $this->routeDef->roles;
    }
}