<?php

declare(strict_types=1);

namespace LM\WebFramework\Model;
use InvalidArgumentException;

/**
 * @todo Rename so it doesn’t imply it needs to be abstract.
 */
final class AbstractEntity implements IModel
{
    public function __construct(
        private array $properties,
        private bool $isNullable = false,
    ) {
    }

    public function getArrayDefinition(): array {
        return $this->properties;
    }

    public function getDateTimeConstraints(): ?array {
        return null;
    }

    public function getListNodeModel(): ?IModel {
        return null;
    }

    public function getIntegerConstraints(): ?array {
        return null;
    }

    public function getStringConstraints(): ?array {
        return null;
    }

    public function isBool(): bool {
        return false;
    }

    public function isNullable(): bool {
        return $this->isNullable;
    }

    public function addProperty(string $key, IModel $model): self {
        if (key_exists($key, $this->properties)) {
            throw new InvalidArgumentException('A property already exists for that key.');
        }
        return new self([$key => $model] + $this->properties, $this->isNullable);
    }

    public function removeProperty(string $keyToRemove): self {
        if (!key_exists($keyToRemove, $this->properties)) {
            throw new InvalidArgumentException('No property with that key exists.');
        }
        return new self(
            array_filter($this->properties, fn ($key) => $key !== $keyToRemove, ARRAY_FILTER_USE_KEY),
            $this->isNullable,
        );
    }
}