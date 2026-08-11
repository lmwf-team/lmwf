<?php

declare(strict_types=1);

namespace LM\WebFramework\Model;

interface IEntity extends IModel
{
    /**
     * @return \LM\WebFramework\Model\IModel[]
     */
    public function getProperties(): array;
}