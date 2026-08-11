<?php

declare(strict_types=1);

namespace LM\WebFramework\Tests\DataStructures;

use LM\WebFramework\DataStructures\Factory\CollectionFactory;
use PHPUnit\Framework\TestCase;

final class CollectionFactoryTest extends TestCase
{
    public function testWithEmptyArray(): void
    {
        $this->assertEquals([], CollectionFactory::createDeepAppList([])->toArray());
        $this->assertEquals([], CollectionFactory::createDeepAppObject([])->toArray());
    }
}
