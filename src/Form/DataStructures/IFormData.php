<?php

declare(strict_types=1);

namespace LM\WebFramework\Form\DataStructures;

/**
 * Data fetched and transformed from a request along with its errors, if any.
 */
interface IFormData
{
    public function getContent(): mixed;

    /**
     * @return string[] An array of string errors, or of arrays of string errors, regarding the data. If the
     */
    public function getErrors(): array;

    public function hasErrors(): bool;
}