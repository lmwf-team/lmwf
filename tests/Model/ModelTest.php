<?php

declare(strict_types=1);

namespace LM\WebFramework\Tests\Validation;

use LM\WebFramework\Model\Type\EntityModel;
use LM\WebFramework\Model\Type\IntModel;
use LM\WebFramework\Model\Type\StringModel;
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
        $this->assertEquals(2, count($model->getProperties()));
    }
}