<?php

declare(strict_types=1);

namespace LMWF\Tests\DataStructures;

use LMWF\DataStructures\Factory\CollectionFactory;
use PHPUnit\Framework\TestCase;

final class CollectionFactoryTest extends TestCase
{
    public function testWithEmptyArray(): void
    {
        self::assertEquals([], CollectionFactory::createDeepAppList([])->toArray());
        self::assertEquals([], CollectionFactory::createDeepAppObject([])->toArray());
    }
}
