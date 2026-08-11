<?php

declare(strict_types=1);

namespace LM\WebFramework\Model\Type;

use InvalidArgumentException;
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
    ) {
        if ($itemModel->isNullable()) {
            throw new InvalidArgumentException('The foreign entity model of an entity list model cannot be nullable.');
        }
        parent::__construct($isNullable);
    }

    public function getItemModel(): ForeignEntityModel
    {
        return $this->itemModel;
    }
}
