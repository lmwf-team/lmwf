<?php

declare(strict_types=1);

namespace LM\WebFramework\Constraint\Type;

use LM\WebFramework\Constraint\Value\IRangeConstraint;

/**
 * Interface for models for which an IRangeConstraint makes sense.
 */
interface ILengthModel extends IModel
{
    public function getRangeConstraint(): ?IRangeConstraint;
}
