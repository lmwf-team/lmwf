<?php

declare(strict_types=1);

namespace LM\WebFramework\Model\Type;

/**
 * Model for data consisting of properties identified with a key.
 * 
 * The model specifies the keys, and the model for each of its properties.
 */
abstract class ArrayModel extends AbstractModel
{
    /**
     * @param array<string, IModel> $properties An associative list of properties.
     * @param bool $isNullable Whether this model is nullable.
     * @todo Check that property keys are strings.
     */
    public function __construct(
        protected array $properties,
        bool $isNullable = false,
    ) {
        parent::__construct($isNullable);
    }

    abstract public function addProperty(string $key, IModel $model): self;

    /**
     * @return array<string, \LM\WebFramework\Model\Type\IModel> An
     * associative array of properties.
     *
     * This guarantees each property has a unique key in the context of the
     * model.
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    abstract public function prune(array $propertiesToKeep): self;

    abstract public function removeProperty(string $keyToRemove): self;
}
