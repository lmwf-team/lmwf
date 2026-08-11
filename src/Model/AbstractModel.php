<?php

declare(strict_types=1);

namespace LM\WebFramework\Model;

abstract class AbstractModel implements IModel
{
    public function __construct(
        private bool $isNullable = false,
    ) {
    }

    public function isNullable(): bool {
        return $this->isNullable;
    }
}