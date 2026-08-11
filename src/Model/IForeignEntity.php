<?php

declare(strict_types=1);

namespace LM\WebFramework\Model;

interface IForeignEntity extends IEntity
{
    public function isLinked(array $mainRow, array $row): bool;
}