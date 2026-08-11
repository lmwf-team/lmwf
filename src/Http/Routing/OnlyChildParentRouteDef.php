<?php

declare(strict_types=1);

namespace LM\WebFramework\Http\Routing;

use InvalidArgumentException;
use LM\WebFramework\Http\Routing\Exception\OnlyChildMustTakeAtLeastOneArgument;

final readonly class OnlyChildParentRouteDef extends RouteDef
{
    /**
     * @param string[] $roles
     */
    public function __construct(
        string $fqcn,
        public ParameterizedRoute $onlyChild,
        array $roles = [],
    ) {
        parent::__construct($fqcn, $roles);
        if (0 === $onlyChild->maxArgs || 0 === $onlyChild->minArgs) {
            throw new OnlyChildMustTakeAtLeastOneArgument("An only child must accept at least one argument.");
        }
    }
}
