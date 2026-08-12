<?php

declare(strict_types=1);

namespace LMWF\Tests\Validation;

use DomainException;
use InvalidArgumentException;
use LMWF\Constraint\Value\RangeConstraint;
use LMWF\Constraint\Type\AbstractModel;
use LMWF\Constraint\Type\BoolModel;
use LMWF\Constraint\Type\DateTimeModel;
use LMWF\Constraint\Type\EntityListModel;
use LMWF\Constraint\Type\EntityModel;
use LMWF\Constraint\Type\ForeignEntityModel;
use LMWF\Constraint\Type\IModel;
use LMWF\Constraint\Type\IntModel;
use LMWF\Constraint\Type\ListModel;
use LMWF\Constraint\Type\StringModel;
use LMWF\Validation\AbstractTypeValidator;
use LMWF\Validation\BoolValidator;
use LMWF\Validation\DateTimeValidator;
use LMWF\Validation\ArrayValidator;
use LMWF\Validation\IntValidator;
use LMWF\Validation\ListValidator;
use LMWF\Validation\StringValidator;
use LMWF\Validation\Validator;
use LMWF\Validation\ValidatorFactory;
use LMWF\Validation\Violation\DictValueViolation;
use LMWF\Validation\Violation\TypeViolation;
use LMWF\Validation\Violation\ValueViolation;
use PHPUnit\Framework\TestCase;

final class ValidatorTest extends TestCase
{
    public function testNullHandlingForNonEntities(): void
    {
        self::assertInstanceOf(TypeViolation::class, new BoolValidator(new BoolModel())->validate(null));
        self::assertNull(new BoolValidator(new BoolModel(isNullable: true))->validate(null));

        self::assertInstanceOf(TypeViolation::class, new StringValidator(new StringModel())->validate(null));
        self::assertNull(new StringValidator(new StringModel(isNullable: true))->validate(null));

        self::assertInstanceOf(TypeViolation::class, new DateTimeValidator(new DateTimeModel())->validate(null));
        self::assertNull(new DateTimeValidator(new DateTimeModel(isNullable: true))->validate(null));

        self::assertInstanceOf(TypeViolation::class, new IntValidator(new IntModel())->validate(null));
        self::assertNull(new IntValidator(new IntModel(isNullable: true))->validate(null));

        self::assertInstanceOf(TypeViolation::class, new ListValidator(new ListModel(new IntModel(),))->validate(null));
        self::assertNull(new ListValidator(new ListModel(new IntModel(), isNullable: true))->validate(null));
    }

    public function testUnsupportedModel(): void
    {
        $this->expectException(DomainException::class);
        new ValidatorFactory()->create(new class () extends AbstractModel {
        });
    }

    public function testStringValidator(): void
    {
        $myString = 'Hello';

        self::assertNull((new StringValidator(new StringModel()))->validate($myString));
        self::assertInstanceOf(ValueViolation::class, new StringValidator(new StringModel(7, 10))->validate($myString));

        self::assertNull((new StringValidator(new StringModel(regex: '[a-zA-Z]+')))->validate($myString));
        self::assertInstanceOf(ValueViolation::class, new StringValidator(new StringModel(regex: '[0-9]+'))->validate($myString));
    }

    public function testArrayValidator(): void
    {
        $entity = [
            'id' => 'hello',
            'age' => 23,
            'sub_entity' => [
                'id' => 'hi',
                'age' => 24,
            ],
        ];
        $model = new EntityModel(
            'entity',
            [
                'id' => new StringModel(),
                'age' => new IntModel(),
                'sub_entity_id' => new StringModel(),
                'sub_entity' => new ForeignEntityModel(
                    new EntityModel(
                        'entity',
                        [
                            'id' => new StringModel(),
                            'age' => new IntModel(),
                        ],
                    ),
                    'id',
                    'sub_entity_id',
                ),
            ],
            'id',
        );
        self::assertInstanceOf(DictValueViolation::class, new ArrayValidator($model)->validate($entity));
        $entity['sub_entity_id'] = 'hi';
        self::assertNull((new ArrayValidator($model))->validate($entity));
    }
}
