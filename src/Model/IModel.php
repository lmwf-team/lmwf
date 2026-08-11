<?php

declare(strict_types=1);

namespace LM\WebFramework\Model;

/**
 * I chose to separate IScalar from IEntity, instead of all putting them in
 * IModel. This is because IScalar objects are treated in very similar way,
 * while IEntity are treated in their own specific way. Having different
 * interfaces to separate them thus makes sense.
 * It also makes testing for the model more readable (instanceof instead of
 * null !== $model->getArrayDefinition()).
 * However, this might be bad OOP practice.
 */
interface IModel
{
    /**
     * @return bool Whether the content is necessarily specified or can be left
     * omitted.
     */
    public function isNullable(): bool;
}