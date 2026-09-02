<?php

declare(strict_types=1);

namespace LMWF\Http\DataStructures;

use LMWF\Repo\IRepo;
use LMWF\Http\Controller\IRoutedController;

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
     */
    public function __construct(
        public string $repoFqcn,
        string $title,
        string $controllerFqcn,
        string $baseUrl,
        bool $indexed,
        bool $inHierarchy,
    ) {
        parent::__construct($title, $controllerFqcn, $baseUrl, $indexed, $inHierarchy);
    }
}
