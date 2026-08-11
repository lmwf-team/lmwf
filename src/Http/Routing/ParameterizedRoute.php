<?php

declare(strict_types=1);

namespace LM\WebFramework\Http\Routing;

use InvalidArgumentException;

/**
 * RouteDef that can take arguments. It cannot have children.
 */
final readonly class ParameterizedRoute extends RouteDef
{
    /**
     * @param string[] $roles
     * @param array<string, self> $routes
     */
    public function __construct(
        string $fqcn,
        array $roles = [],
        public int $minArgs = 0,
        public int $maxArgs = 0,
    ) {
        parent::__construct($fqcn, $roles);
        if ($minArgs < 0) {
            throw new InvalidArgumentException("A ParameterizedRoute's minimum number of arguments cannot be negative, received {$minArgs}.");
        } elseif ($minArgs > $maxArgs) {
            throw new InvalidArgumentException("A ParameterizedRoute's minimum number of arguments (here {$minArgs}) cannot be above its maximum number of arguments (here {$maxArgs}).");
        }
    }
}