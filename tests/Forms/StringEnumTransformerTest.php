<?php

declare(strict_types=1);

namespace LMWF\Tests\Forms;

use LMWF\Constraint\Type\StringEnumModel;
use LMWF\Form\DataStructures\NonExistingEnumCase;
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
        parent::setUp();
        $this->model = new StringEnumModel(StringEnum::cases());
    }

    public function testValidValue(): void
    {
        $transformer = new StringEnumTransformer($this->model, 'enum-field');
        self::assertEquals(
            StringEnum::A,
            $transformer->transformSubmittedData(['enum-field' => StringEnum::A->value], []),
        );
    }

    public function testInvalidValue(): void
    {
        $transformer = new StringEnumTransformer($this->model, 'enum-field');
        self::assertEquals(
            new NonExistingEnumCase('non-existing'),
            $transformer->transformSubmittedData(['enum-field' => 'non-existing'], []),
        );
    }

    public function testNull(): void
    {
        $transformer = new StringEnumTransformer($this->model, 'enum-field');
        self::assertEquals(
            null,
            $transformer->transformSubmittedData(['enum-field' => ''], []),
        );
    }
}
