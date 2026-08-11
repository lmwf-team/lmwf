<?php

declare(strict_types=1);

namespace LM\WebFramework\Model;

use LM\WebFramework\Model\AbstractModel;
use LM\WebFramework\Model\ForeignEntityModel;

final class ListModel extends AbstractModel
{
    public function __construct(
        private ForeignEntityModel $itemModel,
        bool $isNullable = false,
    )
    {
        parent::__construct($isNullable);
    }

    public function getItemModel() : ForeignEntityModel {
        return $this->itemModel;
    }
}