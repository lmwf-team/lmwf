<?php

declare(strict_types=1);

namespace LMWF\DataStructures;

/**
 * @template TKey
 * @template TValue
 */
interface IArrayable extends IDistinguishable
{
    /**
     * @return array<TKey, TValue>
     */
    public function toArray(): array;
}
