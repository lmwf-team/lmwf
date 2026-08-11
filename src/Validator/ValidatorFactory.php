<?php

declare(strict_types=1);

namespace LM\WebFramework\Validator;

use DomainException;
use LM\WebFramework\Constraints\EnumConstraint;
use LM\WebFramework\Constraints\IConstraint;
use LM\WebFramework\Constraints\EntityConstraint;
use LM\WebFramework\Constraints\INotNullConstraint;
use LM\WebFramework\Constraints\INumberConstraint;
use LM\WebFramework\Constraints\IUploadedImageConstraint;
use LM\WebFramework\Constraints\StringConstraint;
use LM\WebFramework\Type\ModelValidator;

final class ValidatorFactory
{
    /**
     * @throws DomainException If no validator is associated with the constraint.
     */
    public function createValidator(IConstraint $constraint, ModelValidator $modelValidator) {
        if ($constraint instanceof INotNullConstraint) {
            return new NotNullValidator($constraint);
        }
        if ($constraint instanceof EntityConstraint) {
            return new EntityValidator($constraint, $modelValidator);
        }
        if ($constraint instanceof StringConstraint) {
            return new StringValidator($constraint);
        }
        if ($constraint instanceof EnumConstraint) {
            return new EnumValidator($constraint);
        }
        if ($constraint instanceof INumberConstraint) {
            return new RangeValidator($constraint);
        }
        if ($constraint instanceof IUploadedImageConstraint) {
            return new UploadedImageValidator($constraint);
        }
        throw new DomainException('Constraint of type ' . get_class($constraint) . ' is unknown.');
    }
}