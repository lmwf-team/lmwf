<?php

declare(strict_types=1);

namespace LMWF\Http\DataStructures;

/**
 * @todo Maybe RouteDef's fqcn and fqcnIfParams could be moved there.
 */
final readonly class PageConf
{
    /**
     * @param list<IPageMetadataConf> $pageMetadataConfs An IPageMetadataConf
     * (an object used to generate the metadata for the page after from the
     * route, after it is generated) for each number of arguments passed to the
     * route. If no IPageMetadataConf is defined for a particular number of
     * arguments, the closest one defined for less arguments is used.
     * @todo Maybe there is no reason that baseUrl is there, maybe it should be
     * in router? Maybe it makes sense because this object is used to generate
     * a page combined with the route, and the route holds no information about
     * the base URL (maybe it should?).
     */
    public function __construct(
        public string $baseUrl,
        public array $pageMetadataConfs,
        public bool $isIndexed = true,
        public bool $isPartOfHierarchy = true,
    ) {
    }

    public static function createStatic(
        string $title,
        string $baseUrl,
        bool $isIndexed = true,
        bool $isPartOfHierarchy = true,
    ): self {
        return new self($baseUrl, [0 => new PageMetadataConfStatic($title)], $isIndexed, $isPartOfHierarchy);
    }
}
