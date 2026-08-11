<?php

declare(strict_types=1);

namespace LM\WebFramework\Tests\Validation;

use DomainException;
use InvalidArgumentException;
use LM\WebFramework\Constraint\Value\RangeConstraint;
use LM\WebFramework\Constraint\Type\AbstractModel;
use LM\WebFramework\Constraint\Type\BoolModel;
use LM\WebFramework\Constraint\Type\DateTimeModel;
use LM\WebFramework\Constraint\Type\EntityListModel;
use LM\WebFramework\Constraint\Type\EntityModel;
use LM\WebFramework\Constraint\Type\ForeignEntityModel;
use LM\WebFramework\Constraint\Type\IModel;
use LM\WebFramework\Constraint\Type\IntModel;
use LM\WebFramework\Constraint\Type\ListModel;
use LM\WebFramework\Constraint\Type\StringModel;
use LM\WebFramework\Validation\AbstractTypeValidator;
use LM\WebFramework\Validation\BoolValidator;
use LM\WebFramework\Validation\DateTimeValidator;
use LM\WebFramework\Validation\EntityValidator;
use LM\WebFramework\Validation\ForeignEntityValidator;
use LM\WebFramework\Validation\IntValidator;
use LM\WebFramework\Validation\ListValidator;
use LM\WebFramework\Validation\StringValidator;
use LM\WebFramework\Validation\Validator;
use LM\WebFramework\Validation\ValidatorFactory;
use LM\WebFramework\Validation\Violation\DictValueViolation;
use LM\WebFramework\Validation\Violation\TypeViolation;
use LM\WebFramework\Validation\Violation\ValueViolation;
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

    public function testEntityValidator(): void
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
        self::assertInstanceOf(DictValueViolation::class, new EntityValidator($model)->validate($entity));
        $entity['sub_entity_id'] = 'hi';
        self::assertNull((new EntityValidator($model))->validate($entity));
    }
}
