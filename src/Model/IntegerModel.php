<?php

namespace LM\WebFramework\Model;

use LM\WebFramework\Constraints\RangeConstraint;

class IntegerModel implements IModel
{
    const MAX = 32767;

    const MIN = -32767;

    private array $constraints;

    private bool $isNullable;

    /**
     * @param LM\WebFramework\Constraints\INumberConstraint[] $constraints
     */
    public function __construct(
        ?array $constraints = null,
        bool $isNullable = false,
    ) {
        $this->constraints = null !== $constraints ? $constraints : [new RangeConstraint(self::MIN, self::MAX)];
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
    
    public function getIntegerConstraints(): array {
        return $this->constraints;
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