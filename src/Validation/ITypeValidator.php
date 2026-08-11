<?php

declare(strict_types=1);

namespace LM\WebFramework\Validation;

use LM\WebFramework\Validation\Violation\TypeViolation;
use LM\WebFramework\Validation\Violation\ValueViolation;

/**
 * Ensures that the given app data is of the model's allowed types and satisfies
 * other constraints of the model.
 */
interface ITypeValidator
{
    public function validate(mixed $data): null|TypeViolation|ValueViolation;
}
