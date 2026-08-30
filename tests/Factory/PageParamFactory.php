<?php

declare(strict_types=1);

namespace LMWF\Tests\Factory;

use LMWF\Http\DataStructures\IPageConf;
use LMWF\Http\DataStructures\PageConf;
use LMWF\Http\DataStructures\StaticPageConf;
use LMWF\Tests\Mocks\Controller2;
use LMWF\Tests\Mocks\UnderscoreController;

/**
 * @todo To rename
 */
final readonly class PageParamFactory
{
    /**
     * @todo To delete
     */
    public static function create(string $title = '_', string $baseUrl = '_', bool $isIndexed = true, bool $isPartOfHierarchy = true): PageConf
    {
        return PageConf::createStatic($title, $baseUrl, $isIndexed, $isPartOfHierarchy);
    }

    public static function createStaticConf(
        string $controllerFqcn = UnderscoreController::class,
        string $title = '_',
        string $baseUrl = '_',
        bool $isIndexed = true,
        bool $isInHierarchy = true,
    ): IPageConf {
        return new StaticPageConf($title, $controllerFqcn, $baseUrl, $isIndexed, $isInHierarchy);
    }
}
