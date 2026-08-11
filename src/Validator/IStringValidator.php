<?php

declare(strict_types=1);

namespace LM\WebFramework\Validator;

interface IStringValidator
{
    /**
     * @return \LM\WebFramework\DataStructures\ConstraintViolation[]
     */
    public function validateString(string $data): array;
}