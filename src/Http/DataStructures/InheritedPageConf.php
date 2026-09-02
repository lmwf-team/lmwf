<?php

declare(strict_types=1);

namespace LMWF\Http\DataStructures;

final readonly class InheritedPageConf
{
    public function isEqual(InheritedPageConf $conf): bool
    {
        return true;
    }
}