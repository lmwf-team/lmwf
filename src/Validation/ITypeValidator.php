<?php

declare(strict_types=1);

namespace LM\WebFramework\Validation;

interface ITypeValidator
{
    /**
     * @return \LM\WebFramework\DataStructures\ConstraintViolation[]
     */
    public function validate(mixed $data): array;
}