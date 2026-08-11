<?php

declare(strict_types=1);

namespace LM\WebFramework\Model;

class DateTimeModel extends AbstractScalar
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

    #[\Override]
    public function getDateTimeConstraints(): ?array {
        return $this->constraints;
    }
}