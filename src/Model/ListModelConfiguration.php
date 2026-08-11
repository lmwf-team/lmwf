<?php

declare(strict_types=1);

namespace LM\WebFramework\Model;

use Closure;

class ListModelConfiguration implements IListModelConfiguration
{
    public function __construct(
        private IModel $model,
        private Closure $isItem = function ($parentRow, $row) {
            return true;
        },
    ) {
    }

    public function isChild(array $parentRow, array $row): bool
    {
        return ($this->isItem)($parentRow, $row);
    }

    public function getReferencedModel(): IModel
    {
        return $this->model;
    }
}