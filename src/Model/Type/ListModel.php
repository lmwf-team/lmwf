<?php

declare(strict_types=1);

namespace LM\WebFramework\Model\Type;

use LM\WebFramework\Model\Type\AbstractModel;

final class ListModel extends AbstractModel
{
    public function __construct(
        private IScalarModel|EntityModel|ListModel $itemModel,
        bool $isNullable = false,
    )
    {
        parent::__construct($isNullable);
    }

    public function getItemModel() : IScalarModel|EntityModel|ListModel
    {
        return $this->itemModel;
    }
}