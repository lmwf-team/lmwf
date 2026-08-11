<?php

namespace LM\WebFramework\Validation;

final class JsonValidator implements ITypeValidator
{
    /**
     * @todo Validate against a JSON schema (given by the model).
     */
    public function validate(mixed $data): array
    {
        return [];
    }
}