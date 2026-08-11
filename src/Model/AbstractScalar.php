<?php

declare(strict_types=1);

namespace LM\WebFramework\Model;

abstract class AbstractScalar implements IScalar
{
    public function __construct(
        private bool $isNullable = false,
    ) {
    }

    public function getDateTimeConstraints(): ?array {
        return null;
    }

    public function getIntegerConstraints(): ?array {
        return null;
    }

    public function getStringConstraints(): ?array {
        return null;
    }

    public function isBool(): bool {
        return false;
    }

    public function isNullable(): bool {
        return $this->isNullable;
    }
}