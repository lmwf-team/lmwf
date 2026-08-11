<?php

declare(strict_types=1);

namespace LM\WebFramework\Http\Routing;

use InvalidArgumentException;

final readonly class Route
{
    /**
     * @param string[] $roles
     * @param array<string, self> $routes
     * @todo PathSegList?
     */
    public function __construct(
        public ParameterizedRoute|ParentRoute $routeDef,
        public array $relevantSegs,
        public ?Route $parent = null,
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
                throw new InvalidArgumentException("Instantiation of ParameterizedRoute has a number of arguments above the maximum ({$nArgs} > {$routeDef->maxArgs}).");
            }
        }
        if (0 === count($relevantSegs) && null !== $parent) {
            throw new InvalidArgumentException("A route that is not root must have relevant path segments.");
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
        if ([] === $this->relevantSegs) {
            return '/';
        }
        return $this->parent?->getPath() . implode('/', $this->relevantSegs);
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return $this->routeDef->roles;
    }
}