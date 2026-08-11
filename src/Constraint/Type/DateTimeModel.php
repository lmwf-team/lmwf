<?php

declare(strict_types=1);

namespace LMWF\Constraint\Type;

final class DateTimeModel extends AbstractModel implements IScalarModel
{
    public function __construct(
        private bool $isNullable = false,
    ) {
        parent::__construct($isNullable);
    }
}
