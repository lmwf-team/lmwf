<?php

declare(strict_types=1);

namespace LM\WebFramework\Tests\DataStructures;

use InvalidArgumentException;
use LM\WebFramework\DataStructures\AppList;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;
use TypeError;
use UnexpectedValueException;

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
        $this->assertEquals($list[0], $appList->getString(0));
        $this->assertEquals($list[1], $appList->getFloat(1));
        $this->assertEquals($list[2], $appList[2]);
        $this->assertEquals($list[3], $appList->getBool(3));
        $this->assertEquals($list[4], $appList->getBool(4));
        $this->assertEquals($list[5], $appList->getInt(5));
        $this->assertEquals($list[6], $appList->getArray(6));
        $this->assertEquals($list[5], $appList->getNullableScalar(5, 'integer'));

        $this->expectException(TypeError::class);
        $appList->getInt(1);

        $this->expectException(OutOfBoundsException::class);
        $appList->getInt(10);

        $this->expectException(OutOfBoundsException::class);
        $appList->getInt('5');
    }
}
