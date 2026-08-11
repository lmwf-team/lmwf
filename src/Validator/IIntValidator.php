<?php

declare(strict_types=1);

namespace LM\WebFramework\Validator;

interface IIntValidator
{
    /**
     * @return \LM\WebFramework\DataStructures\ConstraintViolation[]
     */
    public function validateInt(int $data): array;
}