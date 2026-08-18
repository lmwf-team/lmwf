<?php

declare(strict_types=1);

namespace LMWF\DataStructures;

/**
 * @todo Should go in Http namespace?
 * @todo Remove accessor methods?
 */
final readonly class Page
{
    public function __construct(
        public ?Page $parent,
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
