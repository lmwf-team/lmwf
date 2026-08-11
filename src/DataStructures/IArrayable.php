<?php

declare(strict_types=1);

namespace LM\WebFramework\DataStructures;

interface IArrayable extends IDistinguishable
{
    public function toArray(): array;
}
