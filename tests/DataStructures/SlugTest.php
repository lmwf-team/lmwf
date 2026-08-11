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

    public function testDiacriticETransform(): void
    {
        self::assertEquals(
            'e',
            (new Slug('e', true))->__toString(),
        );
    }

    public function testUnderscoreTransform(): void
    {
        self::assertEquals(
            'a-b',
            (new Slug('a_b', true))->__toString(),
        );
    }
}
