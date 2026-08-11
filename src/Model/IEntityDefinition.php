<?php

declare(strict_types=1);

namespace LM\WebFramework\Model;

interface IEntityDefinition
{
    public function getProperties(): array;

    public function isLinked(array $parentRow, array $row): bool;
}