<?php

declare(strict_types=1);

namespace LMWF\DataStructures;

use InvalidArgumentException;

/**
 * Immutable list of heterogeneous data, guaranteed to have integer property
 * keys, but non-necessarily sequential.
 *
 * @template TValue = mixed
 * @extends ImmutableArray<int, TValue, list<TValue>>
 */
final readonly class AppNonSequentialList extends ImmutableArray
{
    /**
     * @param list<TValue> $data
     */
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $_) {
            if (!is_int($key)) {
                throw new InvalidArgumentException(
                    'Keys of AppNonSequentialList must be integers.',
                );
            }
        }

        parent::__construct($data);
    }
}
