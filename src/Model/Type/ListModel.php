<?php

declare(strict_types=1);

namespace LM\WebFramework\Model\Type;

use LM\WebFramework\Model\Constraints\IRangeConstraint;
use LM\WebFramework\Model\Type\AbstractModel;

/**
 * @todo To delete?
*/
final class ListModel extends AbstractModel
{
    public function __construct(
        private IScalarModel $itemModel,
        private ?IRangeConstraint $rangeConstraint,
        bool $isNullable = false,
    ) {
        parent::__construct($isNullable);
    }

    public function getItemModel() : IScalarModel|EntityModel|ListModel
    {
        return $this->itemModel;
    }

    public function getRangeConstraint(): ?IRangeConstraint
    {
        return $this->rangeConstraint;
    }
}