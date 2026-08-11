<?php

declare(strict_types=1);

namespace LM\WebFramework\Http\Routing;

use InvalidArgumentException;

abstract readonly class Route
{
    /**
     * @param string[] $roles
     */
    public function __construct(
        public string $fqcn,
        public array $roles = [],
    ) {
        foreach ($roles as $role) {
            if (!is_string($role)) {
                throw new InvalidArgumentException("A role must be a string.");
            }
        }
    }
}