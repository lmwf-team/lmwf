<?php

declare(strict_types=1);

namespace LMWF\Tests\Factory;

use LMWF\Http\DataStructures\PageConf;

final readonly class PageParamFactory
{
    public static function create(string $title = '_', string $baseUrl = '_', bool $isIndexed = true, bool $isPartOfHierarchy = true): PageConf
    {
        return PageConf::createStatic($title, $baseUrl, $isIndexed, $isPartOfHierarchy);
    }
}
