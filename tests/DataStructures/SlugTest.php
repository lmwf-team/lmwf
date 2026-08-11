<?php

declare(strict_types=1);

namespace LMWF\Tests\DataStructures;

use LMWF\DataStructures\Slug;
use PHPUnit\Framework\TestCase;

final class SlugTest extends TestCase
{
    public function testSlugModel(): void
    {
        self::assertEquals(
            'mise-a-jour-15-pour-the-crystal-mission',
            (new Slug('Mise à jour 1.5 pour The Crystal Mission', true))->__toString(),
        );
    }
}
