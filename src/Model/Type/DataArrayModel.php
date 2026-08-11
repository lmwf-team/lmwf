<?php

namespace LM\WebFramework\Model\Type;

use InvalidArgumentException;

final class DataArrayModel extends ArrayModel
{
    public function addProperty(string $key, IModel $model): self
    {
        if (key_exists($key, $this->getProperties())) {
            throw new InvalidArgumentException('A property already exists with that key.');
        }
        return new self(
            [$key => $model] + $this->getProperties(),
            $this->isNullable(),
        );
    }

    public function prune(array $propertiesToKeep): self
    {
        return new self(
            array_filter($this->getProperties(), fn ($key) => in_array($key, $propertiesToKeep, strict: true), ARRAY_FILTER_USE_KEY),
            $this->isNullable(),
        );
    }

    public function removeProperty(string $keyToRemove): self
    {
        if (key_exists($keyToRemove, $this->getProperties())) {
            return new self(
                array_filter($this->getProperties(), fn ($key) => $key !== $keyToRemove, ARRAY_FILTER_USE_KEY),
                $this->isNullable(),
            );
        }
        throw new InvalidArgumentException('No property with that key exists.');
    }
}
