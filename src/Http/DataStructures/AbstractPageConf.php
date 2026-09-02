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
    protected bool $isIndexed;
    protected bool $isInHierarchy;
    protected string $baseUrl;
    protected string $controllerFqcn;
    protected string $title;

    /**
     * @param class-string<IRoutedController> $controllerFqcn
     * @param ($from is null ? string : ?string) $title
     * @param ($from is null ? string : ?string) $controllerFqcn
     * @param ($from is null ? string : ?string) $baseUrl
     * @param ($from is null ? bool : ?bool) $isIndexed
     * @param ($from is null ? bool : ?bool) $isInHierarchy
     */
    public function __construct(
        ?string $title = null,
        ?string $controllerFqcn = null,
        ?string $baseUrl = null,
        ?bool $isIndexed = null,
        ?bool $isInHierarchy = null,
        ?IPageConf $from = null,
    ) {
        $this->title = null === $title ? $from->getTitle() : $title;
        $this->controllerFqcn = null === $controllerFqcn ? $from->getControllerFqcn() : $controllerFqcn;
        $this->baseUrl = null === $baseUrl ? $from->getBaseUrl() : $baseUrl;
        $this->isIndexed = null === $isIndexed ? $from->isIndexed() : $isIndexed;
        $this->isInHierarchy = null === $isInHierarchy ? $from->isInHierarchy() : $isInHierarchy;
    }

    #[Override]
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return class-string<IRoutedController>
     */
    public function getControllerFqcn(): string
    {
        return $this->controllerFqcn;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function isIndexed(): bool
    {
        return $this->isIndexed;
    }

    public function isInHierarchy(): bool
    {
        return $this->isInHierarchy;
    }
}
