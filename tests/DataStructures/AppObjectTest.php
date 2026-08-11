<?php

declare(strict_types=1);

namespace LM\WebFramework\Tests\DataStructures;

use InvalidArgumentException;
use LM\WebFramework\DataStructures\AppObject;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;

final class AppObjectTest extends TestCase
{
    public function testCreatingAppObjectWithList(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new AppObject([7, null, true]);
    }

    public function testWithListProperty(): void
    {
        $appArray = [
            'id' => 4,
            'items' => [
                [
                    'name' => 'Item 1',
                ],
                [
                    'name' => 'Item 2',
                ],
            ],
        ];
        $appObject = new AppObject($appArray);
        $this->assertIsList($appObject['items']);
        $this->assertInstanceOf(AppObject::class, $appObject['items'][0]);
        $this->assertInstanceOf(AppObject::class, $appObject['items'][1]);
    }

    public function testNonExistingProperty(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $appArray = new AppObject([
            'id' => 4,
            'items' => [
                [
                    'name' => 'Item 1',
                ],
                [
                    'name' => 'Item 2',
                ],
            ],
        ]);
        $appArray['bro'];
    }

    public function testWithEmptyArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $appArray = new AppObject([
        ]);
    }

    public function testIsEqual(): void
    {
        $appObject1 = new AppObject([
            'id' => 3,
        ]);
        $appObject2 = new AppObject([
            'name' => 'Georges',
        ]);
        $this->assertFalse($appObject1->isEqualTo($appObject2));
        $this->assertFalse($appObject2->isEqualTo($appObject1));
    }
}