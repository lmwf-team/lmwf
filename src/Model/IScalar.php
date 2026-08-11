<?php

declare(strict_types=1);

namespace LM\WebFramework\Model;

interface IScalar extends IModel
{
    /**
     * @return bool Whether the content can be a boolean.
     */
    public function isBool(): bool;

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
     * @return null|LM\WebFramework\Constraints\IStringConstraint[] A list of
     * string constraints if the content can be a string, or null otherwise.
     */
    public function getStringConstraints(): ?array;
}