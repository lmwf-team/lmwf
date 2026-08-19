<?php

declare(strict_types=1);

namespace LMWF\Http\DataStructures;

/**
 * Configuration for a page providing access to a resource fetched from a
 * repository.
 */
final readonly class PageEntConf
{
    /**
     * @param class-string<\LMWF\Repo\IRepo> $repoFqcn The
     * repository to fetch the resource.
     *
     * @todo Make $repoFqcn nullable? Would make no sense as it is PageEntConf.
     */
    public function __construct(
        public string $title,
        public string $repoFqcn,
    ) {
    }
}
