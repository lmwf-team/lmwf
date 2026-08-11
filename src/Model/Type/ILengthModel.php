<?php

declare(strict_types=1);

namespace LM\WebFramework\Model\Type;

use LM\WebFramework\Model\Constraints\IRangeConstraint;

interface ILengthModel extends IModel
{
    public function getRangeConstraint(): ?IRangeConstraint;
}
