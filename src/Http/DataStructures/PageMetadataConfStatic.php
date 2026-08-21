<?php

declare(strict_types=1);

namespace LMWF\Http\DataStructures;

/**
 * Configuration for a page providing access to a resource with a static title.
 */
final readonly class PageMetadataConfStatic implements IPageMetadataConf
{
    /**
     * @param string $title The title of the page.
     */
    public function __construct(
        public string $title,
    ) {
    }

    #[\Override]
    public function getTitle(): string {
        return $this->title;
    }
}
