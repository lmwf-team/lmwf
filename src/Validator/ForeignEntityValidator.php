<?php

declare(strict_types=1);

namespace LM\WebFramework\Validator;

use LM\WebFramework\Model\Type\ForeignEntityModel;

class ForeignEntityValidator implements ITypeValidator
{
    public function __construct(
        private ForeignEntityModel $model,
    ) {  
    }

    /**
     * @todo Find a way to check that the parent ID matches the child ID.
     */
    public function validate(mixed $value): array
    {
        return (new ModelValidator($this->model->getEntityModel()))->validate($value);
    }
}