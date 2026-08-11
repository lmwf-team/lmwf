<?php

declare(strict_types=1);

namespace LM\WebFramework\Model;

use LM\WebFramework\Constraints\StringConstraint;

class StringModel extends AbstractScalar
{
    private array $constraints;

    /**
     * @param \LM\WebFramework\Constraints\IConstraint[] $constraints
     */
    public function __construct(
        ?array $constraints = null,
        bool $isNullable = false,
    ) {
        $this->constraints = null !== $constraints ? $constraints : [new StringConstraint()];
        parent::__construct($isNullable);
    }

    public function getStringConstraints(): array {
        return $this->constraints;
    }
}