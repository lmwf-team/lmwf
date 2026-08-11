<?php

declare(strict_types=1);

namespace LM\WebFramework\Validation;

interface IIntValidator
{
    /**
     * @return \LM\WebFramework\DataStructures\ConstraintViolation[]
     */
    public function validateInt(int $data): array;
}