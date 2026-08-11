<?php

namespace LM\WebFramework\Model;

interface IModel
{
    public function isBool(): bool;

    public function isNullable(): bool;

    /**
     * @return \LM\WebFramework\Model\IModel[] An indexed array of models.
     */
    public function getArrayDefinition(): ?array;

    /**
     * @return \LM\WebFramework\Constraints\IDateTimeConstraint[]
     */
    public function getDateTimeConstraints(): ?array;

    /**
     * @return \LM\WebFramework\Constraints\INumberConstraint[]
     */
    public function getIntegerConstraints(): ?array;

    public function getListNodeModel(): ?IModel;

    /**
     * @return \LM\WebFramework\Constraints\IStringConstraint[] A list of string constraints.
     */
    public function getStringConstraints(): ?array;
}