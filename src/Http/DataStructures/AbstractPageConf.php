<?php

declare(strict_types=1);

namespace LMWF\Http\DataStructures;

use Override;
use LMWF\Http\Controller\IRoutedController;

/**
 * Configuration for a page providing access to a resource fetched from a
 * repository.
 */
abstract readonly class AbstractPageConf implements IPageConf
{
    /**
     * @param class-string<IRoutedController> $controllerFqcn
     */
    public function __construct(
        protected string $title,
        protected string $controllerFqcn,
        protected string $baseUrl,
        protected bool $isIndexed,
        protected bool $isInHierarchy,
    ) {
    }

    #[Override]
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return class-string<IRoutedController>
     */
    #[Override]
    public function getControllerFqcn(): string
    {
        return $this->controllerFqcn;
    }

    #[Override]
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    #[Override]
    public function isIndexed(): bool
    {
        return $this->isIndexed;
    }

    #[Override]
    public function isInHierarchy(): bool
    {
        return $this->isInHierarchy;
    }
}
