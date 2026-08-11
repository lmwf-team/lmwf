<?php

declare(strict_types=1);

namespace LM\WebFramework\Http\Routing;

use InvalidArgumentException;

final readonly class InstantiatedRoute extends Route
{
    /**
     * @param string[] $roles
     * @param array<string, self> $routes
     */
    public function __construct(
        string $fqcn,
        array $roles = [],
        public int $nArgs = 0,
    ) {
        parent::__construct($fqcn, $roles);
        if ($nArgs < 0) {
            throw new InvalidArgumentException("A InstantiatedRoute's number of arguments cannot be negative, received {$nArgs}.");
        }
    }
}