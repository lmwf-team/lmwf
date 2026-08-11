<?php

declare(strict_types=1);

namespace LM\WebFramework\Model\Type;

use InvalidArgumentException;

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
    // {
    //     if (key_exists($key, $this->getProperties())) {
    //         throw new InvalidArgumentException('A property already exists with that key.');
    //     }
    //     return new self(
    //         [$key => $model] + $this->getProperties(),
    //         $this->isNullable(),
    //     );
    // }

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
    // {
    //     return new self(
    //         array_filter($this->getProperties(), fn ($key) => in_array($key, $propertiesToKeep, strict: true), ARRAY_FILTER_USE_KEY),
    //         $this->isNullable(),
    //     );
    // }

    abstract public function removeProperty(string $keyToRemove): self;
    // {
    //     if (key_exists($keyToRemove, $this->getProperties())) {
    //         return new self(
    //             array_filter($this->getProperties(), fn ($key) => $key !== $keyToRemove, ARRAY_FILTER_USE_KEY),
    //             $this->isNullable(),
    //         );
    //     }
    //     throw new InvalidArgumentException('No property with that key exists.');
    // }
}
