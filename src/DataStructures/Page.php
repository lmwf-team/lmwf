<?php

namespace LM\WebFramework\DataStructures;

class Page
{
    public function __construct(
        private ?Page $parent,
        private string $name,
        private string $url,
        private bool $isIndexed = true,
        private bool $isPartOfHierarchy = true,
    ) {
    }

    public function getParent(): ?Page {
        return $this->parent;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getUrl(): string {
        return $this->url;
    }

    public function isIndexed(): bool {
        return $this->isIndexed;
    }

    public function isPartOfHierarchy(): bool {
        return $this->isPartOfHierarchy;
    }
}