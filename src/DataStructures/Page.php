<?php

namespace LM\WebFramework\DataStructures;

class Page
{
    public function __construct(
        private ?Page $parent,
        private string $name,
        private string $url,
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
}