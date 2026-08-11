<?php

namespace LM\WebFramework\Model;

use LM\WebFramework\Constraints\StringConstraint;

class StringModel implements IModel
{
    private array $constraints;

    private bool $isNullable;

    /**
     * @param \LM\WebFramework\Constraints\IConstraint[] $constraints
     */
    public function __construct(
        ?array $constraints = null,
        bool $isNullable = false,
    ) {
        $this->constraints = null !== $constraints ? $constraints : [new StringConstraint()];
        $this->isNullable = $isNullable;
    }

    public function getArrayDefinition(): ?array {
        return null;
    }

    public function getDateTimeConstraints(): ?array {
        return null;
    }

    public function getListNodeModel(): ?IModel {
        return null;
    }

    public function getIntegerConstraints(): ?array {
        return null;
    }

    public function getStringConstraints(): array {
        return $this->constraints;
    }

    public function isBool(): bool {
        return false;
    }

    public function isNullable(): bool {
        return $this->isNullable;
    }
}