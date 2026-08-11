<?php

declare(strict_types=1);

namespace LMWF\Constraint\Type;

use LMWF\Constraint\Value\IRangeConstraint;

/**
 * Interface for models for which an IRangeConstraint makes sense.
 */
interface ILengthModel extends IModel
{
    public function getRangeConstraint(): ?IRangeConstraint;
}
