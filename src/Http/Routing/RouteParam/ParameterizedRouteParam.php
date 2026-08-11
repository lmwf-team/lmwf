<?php

declare(strict_types=1);

namespace LM\WebFramework\Http\Routing\RouteParam;

use InvalidArgumentException;

final readonly class ParameterizedRouteParam
{
    public function __construct(
        public int $nArgsLowerLimit = 0,
        public int $nArgsUpperLimit = 0,
        public ?string $fqcnIfParams = null,
    ) {
        if ($nArgsLowerLimit < 0) {
            throw new InvalidArgumentException("The minimum number of arguments for a route cannot be negative, received {$nArgsLowerLimit}.");
        } elseif ($nArgsLowerLimit > $nArgsUpperLimit) {
            throw new InvalidArgumentException("The minimum number of arguments for a route (here {$nArgsLowerLimit}) cannot be above its maximum number of arguments (here {$nArgsUpperLimit}).");
        }
    }
}