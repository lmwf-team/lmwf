<?php

declare(strict_types=1);

namespace LMWF\DataStructures;

use LMWF\Http\Controller\IRoutedController;

/**
 * @param class-string<IRoutedController> $controllerFqcn
 * @todo Should go in Http namespace?
 * @todo Remove accessor methods?
 */
final readonly class Page
{
    /**
     * @param string $url An absolute URL without trailing slash.
     * @todo Url should be handled by routes? At least it should be computed there.
     */
    public function __construct(
        public ?Page $parent,
        public string $controllerFqcn,
        public string $name,
        public string $url,
        public bool $isIndexed = true,
        public bool $isPartOfHierarchy = true,
    ) {
    }

    public function getParent(): ?Page
    {
        return $this->parent;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function isIndexed(): bool
    {
        return $this->isIndexed;
    }

    public function isPartOfHierarchy(): bool
    {
        return $this->isPartOfHierarchy;
    }
}
