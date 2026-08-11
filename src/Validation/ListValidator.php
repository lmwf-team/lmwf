<?php

declare(strict_types=1);

namespace LM\WebFramework\Validation;

use LM\WebFramework\Validation\ConstraintViolation\ConstraintViolation;
use LM\WebFramework\Model\Type\EntityListModel;
use LM\WebFramework\Model\Type\ListModel;

final class ListValidator implements ITypeValidator
{
    public function __construct(
        private ListModel|EntityListModel $constraint,
    ) {
    }

    public function validate(mixed $data): array
    {
        if (!is_array($data) || !array_is_list($data)) {
            return [
                new ConstraintViolation($this->constraint, 'Value must be a list.'),
            ];
        }
        return [];
    }
}
