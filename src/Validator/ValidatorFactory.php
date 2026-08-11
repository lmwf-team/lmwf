<?php

declare(strict_types=1);

namespace LM\WebFramework\Validator;

use BadMethodCallException;
use DomainException;
use InvalidArgumentException;
use LM\WebFramework\Model\Constraints\IConstraint;
use LM\WebFramework\Model\Type\BoolModel;
use LM\WebFramework\Model\Type\DateTimeModel;
use LM\WebFramework\Model\Type\EntityListModel;
use LM\WebFramework\Model\Type\EntityModel;
use LM\WebFramework\Model\Type\ForeignEntityModel;
use LM\WebFramework\Model\Type\IntModel;
use LM\WebFramework\Model\Type\ListModel;
use LM\WebFramework\Model\Type\StringModel;

final class ValidatorFactory
{
    /**
     * @throws DomainException If no validator is associated with the constraint.
     */
    public function createValidator(IConstraint $constraint): ITypeValidator
    {
        switch ($constraint::class) {
            case EntityModel::class:
            case ForeignEntityModel::class:
                // @todo
                throw new BadMethodCallException();

            case BoolModel::class:
                return new BoolValidator($constraint);
            
            case DateTimeModel::class:
                return new DateTimeValidator($constraint);
            
            case IntModel::class:
                return new IntValidator($constraint);

            case EntityListModel::class:
            case ListModel::class:
                return new ListValidator($constraint);
            
            case StringModel::class:
                return new StringValidator($constraint);

            // case EnumConstraint::class:
            //     return new EnumValidator($constraint);
            
            // case INotNullConstraint::class:
            //     return new NotNullValidator($constraint);

            // case IRangeConstraint::class:
            //     return new RangeValidator($constraint);

            // case IRegexConstraint::class:
            //     return new RegexValidator($constraint);
        }

        throw new InvalidArgumentException('Constraint not supported.');
    }
}