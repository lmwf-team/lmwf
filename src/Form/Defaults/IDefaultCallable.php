<?php

declare(strict_types=1);

namespace LMWF\Form\Defaults;

/**
 * Generates a value for a given field, if the value resulting from its form
 * transformation is null.
 * 
 * @template TReturn
 */
interface IDefaultCallable
{
    /**
     * @param array<mixed> $formData
     * @return TReturn
     */
    public function generateValue(array $formData): mixed;
}