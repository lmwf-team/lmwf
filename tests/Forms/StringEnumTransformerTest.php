<?php

declare(strict_types=1);

namespace LMWF\Tests\Forms;

use LMWF\Constraint\Type\StringEnumModel;
use LMWF\Form\FormFactory;
use LMWF\Form\Transformer\StringEnumTransformer;
use LMWF\Tests\Mocks\StringEnum;
use Override;
use PHPUnit\Framework\TestCase;

final class StringEnumTransformerTest extends TestCase
{
    private StringEnumModel $model;

    #[Override]
    protected function setUp(): void
    {
        $this->model = new StringEnumModel(StringEnum::cases());
        parent::setUp();
    }

    public function testTransformer(): void
    {
        $transformer = new StringEnumTransformer($this->model, 'enum-field');
        self::assertEquals(
            StringEnum::A,
            $transformer->transformSubmittedData(['enum-field' => StringEnum::A->value], []),
        );
    }
}
