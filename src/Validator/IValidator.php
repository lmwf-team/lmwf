<?php

declare(strict_types=1);

namespace LM\WebFramework\Validator;

interface IValidator
{
    /**
     * @return \LM\WebFramework\DataStructures\ConstraintViolation[]
     */
    public function validate(mixed $data): array;
}