<?php

declare(strict_types=1);

namespace LMWF\Tests\Validation;

use LMWF\Constraint\Type\EntityModel;
use LMWF\Constraint\Type\IntModel;
use LMWF\Constraint\Type\StringModel;
use PHPUnit\Framework\TestCase;

final class ModelTest extends TestCase
{
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
