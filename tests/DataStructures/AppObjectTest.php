<?php

declare(strict_types=1);

namespace LM\WebFramework\Tests\DataStructures;

use InvalidArgumentException;
use LM\WebFramework\DataStructures\AppList;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\DataStructures\Factory\CollectionFactory;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;

final class AppObjectTest extends TestCase
{
    public function testCreatingAppObjectWithList(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new AppObject([7, null, true]);
    }

    public function testCreatingtWithNonStringKeys(): void
    {
        $array = [
            'test_key' => 'test_value',
            2 => 'test_value',
        ];
        $this->expectException(InvalidArgumentException::class);
        new AppObject($array);
    }

    public function testAccessingSlightlyDifferentKey(): void
    {
        $array = [
            'test_key' => 'test_value',
            '2' => 'test_value',
        ];
        $this->expectException(InvalidArgumentException::class);
        new AppObject($array);
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
        $appObject = CollectionFactory::createDeepAppObject($appArray);
        self::assertInstanceOf(AppList::class, $appObject['items']);
        self::assertInstanceOf(AppObject::class, $appObject['items'][0]);
        self::assertInstanceOf(AppObject::class, $appObject['items'][1]);
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
        $appArray['item'];
    }

    public function testIsEqual(): void
    {
        $appObject1 = CollectionFactory::createDeepAppObject([
            'id' => 3,
        ]);
        $appObject1Copy = CollectionFactory::createDeepAppObject([
            'id' => 3,
        ]);
        $appObject2 = CollectionFactory::createDeepAppObject([
            'name' => 'Georges',
        ]);
        self::assertFalse($appObject1->isEqual($appObject2));
        self::assertFalse($appObject2->isEqual($appObject1));
        self::assertTrue($appObject1->isEqual($appObject1));
        self::assertTrue($appObject1->isEqual($appObject1Copy));
    }
}
