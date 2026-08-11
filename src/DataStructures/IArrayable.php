<?php

namespace LM\WebFramework\DataStructures;

interface IArrayable extends IDistinguishable
{
    public function toArray(): array;
}
