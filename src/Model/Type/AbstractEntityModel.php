<?php

declare(strict_types=1);

namespace LM\WebFramework\Model\Type;

use InvalidArgumentException;

abstract class AbstractEntityModel extends AbstractModel
{
    /**
     * @param string $prefix The model slug identifier.
     * @param IModel[] $properties An associative list of properties.
     * @param bool $isNullable Whether the entity is nullable.
     */
    public function __construct(
        private string $identifier,
        private array $properties,
        private string $idKey = 'id',
        bool $isNullable = false,
    ) {
        if (!key_exists($idKey, $properties)) {
            throw new InvalidArgumentException('Specified ID key is not among the model’s properties.');
        }
        parent::__construct($isNullable);
    }

    /**
     * @return string An identifier for the model.
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getIdKey(): string
    {
        return $this->idKey;
    }

    /**
     * @return \LM\WebFramework\Model\Type\IModel[] An associative array of
     * properties. This guarantees each property has a unique key in the context
     * of the model.
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    public function addProperty(string $key, IModel $model): self {
        if (key_exists($key, $this->properties)) {
            throw new InvalidArgumentException('A property already exists with that key.');
        }
        return new self(
            $this->identifier,
            [$key => $model] + $this->properties,
            $this->getIdKey(),
            $this->isNullable(),
        );
    }

    public function removeProperty(string $keyToRemove): self {
        if (key_exists($keyToRemove, $this->properties)) {
            return new self(
                $this->identifier,
                array_filter($this->properties, fn ($key) => $key !== $keyToRemove, ARRAY_FILTER_USE_KEY),
                $this->getIdKey(),
                $this->isNullable(),
            );
        }
        throw new InvalidArgumentException('No property with that key exists.');
    }
}