<?php

declare(strict_types=1);

namespace LMWF\Form\Transformer;

abstract class AbstractNamedTransformer implements IFormTransformer
{
    public function __construct(
        private string $name,
    ) {
    }

    /**
     * @todo Use PHP8.4 notation
     */
    public function getName(): string
    {
        return $this->name;
    }
}
