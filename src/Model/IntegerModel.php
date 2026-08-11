<?php

declare(strict_types=1);

namespace LM\WebFramework\Model;

use LM\WebFramework\Constraints\RangeConstraint;

class IntegerModel extends AbstractScalar
{
    const MAX = 32767;

    const MIN = -32767;

    private array $constraints;

    /**
     * @param LM\WebFramework\Constraints\INumberConstraint[] $constraints
     */
    public function __construct(
        ?array $constraints = null,
        bool $isNullable = false,
    ) {
        $this->constraints = null !== $constraints ? $constraints : [new RangeConstraint(self::MIN, self::MAX)];
        parent::__construct($isNullable);
    }

    #[\Override]
    public function getIntegerConstraints(): array {
        return $this->constraints;
    }
}