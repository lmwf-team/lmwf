<?php

declare(strict_types=1);

namespace LM\WebFramework\Http\Routing;

use LM\WebFramework\DataStructures\AppObject;
use UnexpectedValueException;

final readonly class Route
{
    /**
     * @param string[] $roles
     * @param array<string, self> $routes
     */
    public function __construct(
        public string $fqcn,
        public array $roles = [],
        public array $routes = [],
    ) {
    }
}