<?php

declare(strict_types=1);

namespace LM\WebFramework\Model\Constraints;

interface IEnumConstraint extends IStringConstraint
{
    /**
     * @return enum[] $enumCases List of permitted string values.
     */
    public function getValues(): array;
}