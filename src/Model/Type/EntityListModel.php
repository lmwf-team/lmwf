<?php

declare(strict_types=1);

namespace LM\WebFramework\Model\Type;

use LM\WebFramework\Model\Type\AbstractModel;
use LM\WebFramework\Model\Type\ForeignEntityModel;

/**
 * @todo Create IListModel interface for lists, with certain constraints like
 * size.
 */
final class EntityListModel extends AbstractModel
{
    public function __construct(
        private ForeignEntityModel $itemModel,
        bool $isNullable = false,
    )
    {
        parent::__construct($isNullable);
    }

    public function getItemModel() : ForeignEntityModel
    {
        return $this->itemModel;
    }
}