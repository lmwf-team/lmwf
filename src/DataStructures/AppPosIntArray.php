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
 * @todo Add tests
 * @todo Maybe not necessary, could be done with PHPStan?
 */
final readonly class AppPosIntArray extends ImmutableArray
{
    /**
     * @param list<TValue> $data
     */
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $_) {
            if (!is_int($key)) {
                throw new InvalidArgumentException(
                    'Keys of AppPosIntArray must be integers.',
                );
            }
        }

        parent::__construct($data);
    }
}
