<?php

declare(strict_types=1);

namespace LM\WebFramework\Constraints;

use InvalidArgumentException;
use LM\WebFramework\Model\IModel;

final class EntityConstraint implements IConstraint
{
    /**
     * @param array<IModel> $properties
     */
    public function __construct(
        private array $properties,
    ) {
        foreach ($properties as $key => $property) {
            if (!is_string($key) || !($property instanceof IModel)) {
                throw new InvalidArgumentException();
            }
        }
    }

    /**
     * @return array<IModel> An array of properties, indexed by property name.
     */
    public function getProperties(): array {
        return $this->properties;
    }
}