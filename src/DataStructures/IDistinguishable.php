<?php

namespace LM\WebFramework\DataStructures;

interface IDistinguishable
{
    /**
     * @param mixed $value The value to compare the object with.
     * @return bool Whether the instance represents the same data as the
     * parameter.
     */
    public function isEqual(mixed $value): bool;
}