<?php

declare(strict_types=1);

namespace LM\WebFramework\Model;

interface IListModelConfiguration
{
    /**
     * Determines whether the given row is
     */
    public function isChild(array $parentRow, array $row): bool;

    public function getReferencedModel(): IModel;
}