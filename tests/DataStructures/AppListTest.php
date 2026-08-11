<?php

declare(strict_types=1);

namespace LMWF\Tests\DataStructures;

use InvalidArgumentException;
use LMWF\DataStructures\AppList;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;
use TypeError;

final class AppListTest extends TestCase
{
    public function testWithListIndexedFrom1(): void
    {
        $notAList = [
            1 => 88,
            2 => null,
        ];
        $this->expectException(InvalidArgumentException::class);
        new AppList($notAList);
    }

    public function testWithMissingIndices(): void
    {
        $notAList = [
            0 => true,
            2 => false,
        ];
        $this->expectException(InvalidArgumentException::class);
        new AppList($notAList);
    }

    public function testValidGets(): void
    {
        $list = [
            'firstValue',
            7.77,
            null,
            true,
            false,
            90,
            [
                1,
                2,
                3,
            ],
        ];
        $appList = new AppList($list);
        self::assertEquals($list[0], $appList->getString(0));
        self::assertEquals($list[1], $appList->getFloat(1));
        self::assertEquals($list[2], $appList[2]);
        self::assertEquals($list[3], $appList->getBool(3));
        self::assertEquals($list[4], $appList->getBool(4));
        self::assertEquals($list[5], $appList->getInt(5));
        self::assertEquals($list[6], $appList->getArray(6));
        self::assertEquals($list[5], $appList->getNullableScalar(5, 'integer'));

        $this->expectException(TypeError::class);
        $appList->getInt(1);

        $this->expectException(OutOfBoundsException::class);
        $appList->getInt(10);

        $this->expectException(OutOfBoundsException::class);
        $appList->getInt('5');
    }
}
