<?php

declare(strict_types=1);

namespace LM\WebFramework\Model;

use Closure;

final class SubModelDefinition
{
    public function __construct(
        private IModel $model,
        private Closure $isLinked,
    ) {
    }

    public function getModel(): IModel
    {
        return $this->model;
    }

    public function isLinked(array $parentRow, array $row): bool
    {
        return ($this->isLinked)($parentRow, $row);
    }
}