<?php

namespace LM\WebFramework\Model;

interface IEntity
{
    /**
     * @return \LM\WebFramework\Model\IModel[]
     */
    public function getProperties(): array;
}