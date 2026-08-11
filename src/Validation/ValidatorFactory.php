<?php

declare(strict_types=1);

namespace LMWF\Validation;

use DomainException;
use LMWF\Constraint\Type\ArrayModel;
use LMWF\Constraint\Type\BoolModel;
use LMWF\Constraint\Type\DataArrayModel;
use LMWF\Constraint\Type\DateTimeModel;
use LMWF\Constraint\Type\EntityListModel;
use LMWF\Constraint\Type\EntityModel;
use LMWF\Constraint\Type\ForeignEntityModel;
use LMWF\Constraint\Type\IModel;
use LMWF\Constraint\Type\IntModel;
use LMWF\Constraint\Type\ListModel;
use LMWF\Constraint\Type\StringModel;

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
