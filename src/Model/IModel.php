<?php

namespace LM\WebFramework\Model;

interface IModel
{
    /**
     * @return bool Whether the content can be a boolean.
     */
    public function isBool(): bool;

    /**
     * @return bool Whether the content is necessarily specified or can be left omitted.
     */
    public function isNullable(): bool;

    /**
     * @return null|\LM\WebFramework\Model\IModel[] An indexed array of models if the
     * content can be an entity, or null otherwise.
     */
    public function getArrayDefinition(): ?array;

    /**
     * @return null|\LM\WebFramework\Constraints\IDateTimeConstraint[] A list of
     * constraints if the content can be a date, or null otherwise.
     */
    public function getDateTimeConstraints(): ?array;

    /**
     * @return null|\LM\WebFramework\Constraints\INumberConstraint[] A list of
     * numeric constraints if the content can be a number, or null otherwise.
     */
    public function getIntegerConstraints(): ?array;

    /**
     * @return null|IModel The model of each list item if the content can be a
     * list, or null otherwise.
     */
    public function getListNodeModel(): ?IModel;

    /**
     * @return null|LM\WebFramework\Constraints\IStringConstraint[] A list of
     * string constraints if the content can be a string, or null otherwise.
     */
    public function getStringConstraints(): ?array;
}