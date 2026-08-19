<?php

declare(strict_types=1);

namespace LMWF\Http\DataStructures;

/**
 * @todo Maybe RouteDef's fqcn and fqcnIfParams could be moved there.
 */
final readonly class PageConf
{
    /**
     * @todo Maybe there is no reason that baseUrl is there, maybe it should be
     * in router.
     */
    public function __construct(
        public string $title,
        public string $baseUrl,
        public bool $isIndexed = true,
        public bool $isPartOfHierarchy = true,
        public ?PageEntConf $entConf = null,
    ) {
    }
}
