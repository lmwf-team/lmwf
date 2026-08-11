<?php

namespace LM\WebFramework\Database\Exceptions;

use InvalidArgumentException;
use LM\WebFramework\Model\IModel;

class InvalidDbDataException extends InvalidArgumentException
{
    public function __construct(mixed $dbData, IModel $model, ?string $propertyName = null)
    {
        $modelClass = get_class($model);
        parent::__construct(
            "DB data is not of any type supported by the '{$modelClass}' with key '{$propertyName}'\n" .
            var_export($dbData, true)
        );
    }
}