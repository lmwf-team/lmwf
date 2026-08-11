<?php

declare(strict_types=1);

namespace LM\WebFramework\Model\Type;

use LM\WebFramework\Model\Constraints\INotNullConstraint;
use LM\WebFramework\Model\Constraints\NotNullConstraint;

abstract class AbstractModel implements IModel
{
    private ?INotNullConstraint $notNullConstraint;

    public function __construct(
        bool $isNullable = false,
    ) {
        $this->notNullConstraint = $isNullable ? null : new NotNullConstraint();
    }

    public function getNotNullConstraint(): ?INotNullConstraint
    {
        return $this->notNullConstraint;
    }

    public function isNullable(): bool
    {
        return null === $this->notNullConstraint;
    }
}