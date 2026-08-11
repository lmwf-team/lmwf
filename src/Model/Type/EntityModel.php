<?php

declare(strict_types=1);

namespace LM\WebFramework\Model\Type;

use InvalidArgumentException;

/**
 * @todo Disallow sub EntityModel sub properties that are not contained within
 * a ForeignEntityModel?
 */
final class EntityModel extends ArrayModel
{
    /**
     * @param string $prefix The model slug identifier.
     * @param IModel[] $properties An associative list of properties.
     * @param bool $isNullable Whether the entity is nullable.
     * @todo Check that property keys are strings.
     */
    public function __construct(
        private string $identifier,
        array $properties,
        private string $idKey = 'id',
        bool $isNullable = false,
    ) {
        if (!key_exists($idKey, $properties)) {
            throw new InvalidArgumentException('Specified ID key is not among the model’s properties.');
        }
        parent::__construct($properties, $isNullable);
    }

    public function addItselfAsProperty(
        string $key,
        string $referenceKeyInChild,
        string $referenceKeyInParent,
        bool $isNullable,
    ): self {
        $m = new self(
            $this->getIdentifier(),
            $this->getProperties(),
            $this->getIdKey(),
            $this->isNullable(),
        );
        $m->properties[$key] = new ForeignEntityModel($m, $referenceKeyInChild, $referenceKeyInParent, $isNullable);
        return $m;
    }

    #[\Override]
    public function addProperty(string $key, IModel $model): self
    {
        if (key_exists($key, $this->getProperties())) {
            throw new InvalidArgumentException('A property already exists with that key.');
        }
        return new self(
            $this->getIdentifier(),
            [$key => $model] + $this->getProperties(),
            $this->getIdKey(),
            $this->isNullable(),
        );
    }

    /**
     * @return string An identifier for the model.
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return string The key of the model property that serves as its
     * primary key.
     */
    public function getIdKey(): string
    {
        return $this->idKey;
    }

    #[\Override]
    public function prune(array $propertiesToKeep): self
    {
        return new self(
            $this->getIdentifier(),
            array_filter($this->getProperties(), fn ($key) => in_array($key, $propertiesToKeep, strict: true), ARRAY_FILTER_USE_KEY),
            $this->getIdKey(),
            $this->isNullable(),
        );
    }

    #[\Override]
    public function removeProperty(string $keyToRemove): self
    {
        if (key_exists($keyToRemove, $this->getProperties())) {
            return new self(
                $this->getIdentifier(),
                array_filter($this->getProperties(), fn ($key) => $key !== $keyToRemove, ARRAY_FILTER_USE_KEY),
                $this->getIdKey(),
                $this->isNullable(),
            );
        }
        throw new InvalidArgumentException('No property with that key exists.');
    }

    public function setIdentifier(string $newIdentifier): self
    {
        return new self(
            $newIdentifier,
            $this->properties,
            $this->idKey,
            $this->isNullable()
        );
    }
}
