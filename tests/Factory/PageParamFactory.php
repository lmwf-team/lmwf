<?php

declare(strict_types=1);

namespace LMWF\Tests\Factory;

use LMWF\DataStructures\PageParam;

final readonly class PageParamFactory
{
    public static function create(string $title = '_', string $baseUrl = '_', bool $isIndexed = true, bool $isPartOfHierarchy = true): PageParam
    {
        return new PageParam($title, $baseUrl, $isIndexed, $isPartOfHierarchy);
    }
}