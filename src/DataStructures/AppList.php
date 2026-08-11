<?php

namespace LM\WebFramework\DataStructures;

use InvalidArgumentException;

/**
 * Immutable list.
 * 
 * All property keys are sequential integers.
 */
class AppList extends ImmutableArray
{
    public function __construct(array $data)
    {
        if (!array_is_list($data)) {
            throw new InvalidArgumentException('Constructor must receive a list.');
        }

        parent::__construct($data);
    }
}