<?php

declare(strict_types=1);

namespace LMWF\Constraint\Type;

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

    /**
     * @param non-decimal-int-string $key
     */
    abstract public function addProperty(string $key, IModel $model): self;

    /**
     * This guarantees each property has a unique key in the context of the
     * model.
     *
     * @return array<string, \LMWF\Constraint\Type\IModel> An
     * associative array of properties.
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @param list<string> $propertiesToKeep
     */
    abstract public function prune(array $propertiesToKeep): self;

    /**
     * @param non-decimal-int-string $keyToRemove
     */
    abstract public function removeProperty(string $keyToRemove): self;
}
