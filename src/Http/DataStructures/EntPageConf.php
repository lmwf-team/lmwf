<?php

declare(strict_types=1);

namespace LMWF\Http\DataStructures;

use LMWF\Repo\IRepo;

/**
 * Configuration for a page providing access to a resource fetched from a
 * repository.
 */
final readonly class EntPageConf extends AbstractPageConf
{
    /**
     * @param class-string<IRepo> $repoFqcn The
     * repository to fetch the resource
     * @param class-string<IRoutedController> $controllerFqcn
     * @param ($from is null ? string : ?string) $title
     * @param ($from is null ? string : ?string) $controllerFqcn
     * @param ($from is null ? string : ?string) $baseUrl
     * @param ($from is null ? bool : ?bool) $indexed
     * @param ($from is null ? bool : ?bool) $inHierarchy
     */
    public function __construct(
        public string $repoFqcn,
        ?string $title = null,
        ?string $controllerFqcn = null,
        ?string $baseUrl = null,
        ?bool $indexed = null,
        ?bool $inHierarchy = null,
        ?IPageConf $from = null,
    ) {
        $this->repoFqcn = $repoFqcn;

        parent::__construct($title, $controllerFqcn, $baseUrl, $indexed, $inHierarchy, $from);
    }
}
