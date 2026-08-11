<?php

declare(strict_types=1);

namespace LM\WebFramework\Validation;

use DomainException;
use LM\WebFramework\Constraint\Type\ArrayModel;
use LM\WebFramework\Constraint\Type\BoolModel;
use LM\WebFramework\Constraint\Type\DataArrayModel;
use LM\WebFramework\Constraint\Type\DateTimeModel;
use LM\WebFramework\Constraint\Type\EntityListModel;
use LM\WebFramework\Constraint\Type\EntityModel;
use LM\WebFramework\Constraint\Type\ForeignEntityModel;
use LM\WebFramework\Constraint\Type\IModel;
use LM\WebFramework\Constraint\Type\IntModel;
use LM\WebFramework\Constraint\Type\ListModel;
use LM\WebFramework\Constraint\Type\StringModel;

final class ValidatorFactory
{
    public function create(IModel $model): ITypeValidator
    {
        if ($model instanceof DataArrayModel) {
            return new EntityValidator($model);
        } elseif ($model instanceof EntityModel) {
            return new EntityValidator($model);
        } elseif ($model instanceof BoolModel) {
            return new BoolValidator($model);
        } elseif ($model instanceof DateTimeModel) {
            return new DateTimeValidator($model);
        } elseif ($model instanceof EntityListModel) {
            return new ListValidator($model);
        } elseif ($model instanceof ForeignEntityModel) {
            return new ForeignEntityValidator($model);
        } elseif ($model instanceof IntModel) {
            return new IntValidator($model);
        } elseif ($model instanceof ListModel) {
            return new ListValidator($model);
        } elseif ($model instanceof StringModel) {
            return new StringValidator($model);
        }
        throw new DomainException('Model is not of type known to the validator.');
    }
}
