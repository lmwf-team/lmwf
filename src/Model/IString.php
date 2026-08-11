<?php

namespace LM\WebFramework\Model;

interface IString
{
    /**
     * @return \LM\WebFramework\Constraints\IConstraint[]
     */
    public function getStringConstraints(): array;
}