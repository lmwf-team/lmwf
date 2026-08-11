<?php

namespace LM\WebFramework\Tests\DataStructures;

use LM\WebFramework\DataStructures\Slug;
use PHPUnit\Framework\TestCase;

class SlugTest extends TestCase
{
    public function testSlugModel(): void
    {
        $this->assertEquals(
            'mise-a-jour-15-pour-the-crystal-mission',
            (new Slug('Mise à jour 1.5 pour The Crystal Mission', true))->__toString(),
        );
    }
}