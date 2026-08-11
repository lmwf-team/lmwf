<?php

declare(strict_types=1);

namespace LMWF\Form\Defaults;

use LMWF\DataStructures\Slug;
use UnexpectedValueException;

/**
 * @todo Should implement IDefaultCallable<string>
 * @implements IDefaultCallable<mixed>
 */
final readonly class SlugDefaultCallable implements IDefaultCallable
{
    /**
     * @param non-decimal-int-string $slugSourceFieldId
     */
    public function __construct(
        private string $slugSourceFieldId,
    ) {
    }

    public function generateValue(array $formData): mixed
    {
        $slugSourceFieldValue = $formData[$this->slugSourceFieldId];
        if (!is_string($slugSourceFieldValue) && null !== $slugSourceFieldValue) {
            throw new UnexpectedValueException("Slug source field value must either be a string or null.");
        }
        return null !== $slugSourceFieldValue ? (new Slug($slugSourceFieldValue, true))->__toString() : null;
    }
}