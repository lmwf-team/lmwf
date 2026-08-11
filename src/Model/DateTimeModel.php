<?php

declare(strict_types=1);

namespace LM\WebFramework\Model;

final class DateTimeModel extends AbstractModel implements IScalarModel
{
    /**
     * @param \LM\WebFramework\Constraints\IDateTimeConstraint[] $constraints
     */
    public function __construct(
        private array $constraints = [],
        private bool $isNullable = false,
    ) {
        parent::__construct($isNullable);
    }

    public function getDateTimeConstraints(): array {
        return $this->constraints;
    }
}