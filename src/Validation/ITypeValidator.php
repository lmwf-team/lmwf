<?php

declare(strict_types=1);

namespace LM\WebFramework\Validation;

/**
 * Validator for app data.
 * 
 * Validates that the app data it is passed conforms to the model it
 * is instantiated with, by returning a potentially empty list of
 * validation failures.
 * 
 * @todo Rename to IAppDataValidator?
 */
interface ITypeValidator
{
    /**
     * @return \LM\WebFramework\DataStructures\ConstraintViolation[]
     */
    public function validate(mixed $data): array;
}
