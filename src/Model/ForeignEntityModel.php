<?php

declare(strict_types=1);

namespace LM\WebFramework\Model;

use InvalidArgumentException;

final class ForeignEntityModel extends AbstractEntityModel
{
    public function __construct(
        private string $childIdKey,
        private string $parentIdKey,
        string $identifier,
        array $properties,
        private string $idKey = 'id',
        bool $isNullable = false,
    ) {
        parent::__construct($identifier, $properties, $idKey, $isNullable);
    }

    public function getChildIdKey(): string
    {
        return $this->childIdKey;
    }

    public function getParentIdKey(): string
    {
        return $this->parentIdKey;
    }
}