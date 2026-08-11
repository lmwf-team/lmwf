<?php

declare(strict_types=1);

namespace LM\WebFramework\Model\Type;

abstract class ArrayModel extends AbstractModel
{
    /**
     * @param IModel[] $properties An associative list of properties.
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
