<?php

declare(strict_types=1);

namespace LMWF\Http\DataStructures;

use Override;

/**
 * Configuration for a page providing access to a resource fetched from a
 * repository.
 */
final readonly class PageMetadataConfEnt implements IPageMetadataConf
{
    /**
     * @param class-string<\LMWF\Repo\IRepo> $repoFqcn The
     * repository to fetch the resource
     */
    public function __construct(
        public string $title,
        public string $repoFqcn,
    ) {
    }

    #[Override]
    public function getTitle(): string {
        return $this->title;
    }
}
