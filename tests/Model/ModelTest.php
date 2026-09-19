<?php

declare(strict_types=1);

namespace LMWF\Tests\Model;

use LMWF\Constraint\Type\EntityModel;
use LMWF\Constraint\Type\IntModel;
use LMWF\Constraint\Type\StringEnumModel;
use LMWF\Constraint\Type\StringModel;
use LMWF\ErrorHandling\ExceptionCode;
use LMWF\Tests\Mocks\IntEnum;
use PHPUnit\Framework\TestCase;

final class ModelTest extends TestCase
{
    public function testStringEnumModelWithStringList(): void
    {
        $this->expectExceptionCode(ExceptionCode::CONSTRAINTS_TYPE_STRINGENUM_BAD_CASE->value);
        new StringEnumModel(['string']);
    }

    public function testStringEnumModelWithIntEnum(): void
    {
        $this->expectExceptionCode(ExceptionCode::CONSTRAINTS_TYPE_STRINGENUM_BAD_CASE->value);
        new StringEnumModel(IntEnum::cases());
    }

    public function testEntityMethods(): void
    {
        $model = new EntityModel(
            'model',
            [
                'id' => new StringModel(),
                'name' => new StringModel(),
                'age' => new IntModel(),
            ],
        );
        $model = $model->prune(['id', 'name']);
        self::assertEquals(2, count($model->getProperties()));
    }
}
