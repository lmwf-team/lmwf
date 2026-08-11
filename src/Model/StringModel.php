<?php

declare(strict_types=1);

namespace LM\WebFramework\Model;

use LM\WebFramework\Constraints\StringConstraint;

final class StringModel extends AbstractModel implements IScalarModel
{
    private array $constraints;

    // new StringConstraint(minLength: 1, regex: StringConstraint::REGEX_DASHES),

    /**
     * @param \LM\WebFramework\Constraints\IConstraint[] $constraints
     */
    public function __construct(
        array $constraints = [],
        bool $isNullable = false,
    ) {
        $this->constraints = $constraints;
        parent::__construct($isNullable);
    }

    public function getStringConstraints(): array {
        return $this->constraints;
    }
}