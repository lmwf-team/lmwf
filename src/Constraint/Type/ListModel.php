<?php

declare(strict_types=1);

namespace LMWF\Constraint\Type;

use LMWF\Constraint\Value\IRangeConstraint;
use LMWF\Constraint\Type\AbstractModel;

/**
 * @todo Rename to ScalarListModel? Merge with EntityListModel?
*/
final class ListModel extends AbstractModel
{
    public function __construct(
        private IScalarModel $itemModel,
        private ?IRangeConstraint $rangeConstraint = null,
        bool $isNullable = false,
    ) {
        parent::__construct($isNullable);
    }

    public function getItemModel(): IScalarModel
    {
        return $this->itemModel;
    }

    public function getRangeConstraint(): ?IRangeConstraint
    {
        return $this->rangeConstraint;
    }
}
