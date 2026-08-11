<?php

declare(strict_types=1);

namespace LM\WebFramework\Validation;

use LM\WebFramework\Constraint\Type\ForeignEntityModel;
use LM\WebFramework\Validation\Violation\TypeViolation;
use LM\WebFramework\Validation\Violation\ValueViolation;
use Override;

final readonly class ForeignEntityValidator extends AbstractTypeValidator
{
    public function __construct(
        private ForeignEntityModel $model,
    ) {
        parent::__construct($model->getNotNullConstraint());
    }

    /**
     * @todo Find a way to check that the parent ID matches the child ID.
     */
    #[Override]
    public function validateNonNullValue(array|bool|float|int|object|string $value): null|TypeViolation|ValueViolation
    {
        return new ValidatorFactory()->create($this->model->getEntityModel())->validate($value);
    }
}
