<?php

declare(strict_types=1);

namespace LM\WebFramework\Tests\Validation;

use InvalidArgumentException;
use LM\WebFramework\Model\Constraints\RangeConstraint;
use LM\WebFramework\Model\Type\AbstractModel;
use LM\WebFramework\Model\Type\BoolModel;
use LM\WebFramework\Model\Type\EntityModel;
use LM\WebFramework\Model\Type\ForeignEntityModel;
use LM\WebFramework\Model\Type\IntModel;
use LM\WebFramework\Model\Type\StringModel;
use LM\WebFramework\Validation\Validator;
use PHPUnit\Framework\TestCase;

final class ValidatorTest extends TestCase
{
    public function testNullable(): void
    {
        $this->assertEmpty((new Validator(new BoolModel(true)))->validate(null), 'Null should be allowed.');
        $this->assertNotEmpty((new Validator(new BoolModel()))->validate(null), 'Null should NOT be allowed.');
        $this->assertNotEmpty((new Validator(new BoolModel()))->validate(null), 'Null should NOT be allowed.');
    }

    public function testUnsupportedModel(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Validator(new class () extends AbstractModel {
        });
    }

    public function testStringValidator(): void
    {
        $myString = 'Hello';
        $this->assertEmpty((new Validator(new StringModel()))->validate($myString));
        $this->assertNotEmpty((new Validator(new StringModel(7, 10)))->validate($myString));
        $this->assertEmpty((new Validator(new StringModel(regex: '[a-zA-Z]+')))->validate($myString));
        $this->assertNotEmpty((new Validator(new StringModel(regex: '[0-9]+')))->validate($myString));

        $this->expectException(InvalidArgumentException::class);
        new RangeConstraint(10, 5);
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
        $this->assertNotEmpty((new Validator($model))->validate($entity));
        $entity['sub_entity_id'] = 'hi';
        $this->assertEmpty((new Validator($model))->validate($entity));
    }
}
