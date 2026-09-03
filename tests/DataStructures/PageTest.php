<?php

declare(strict_types=1);

namespace LMWF\Tests\DataStructures;

use LMWF\DataStructures\Page;
use LMWF\ErrorHandling\ExceptionCode;
use PHPUnit\Framework\TestCase;

final class PageTest extends TestCase
{
    public function testConstructorWithNonHierarchichalPage(): void
    {
        $this->expectExceptionCode(ExceptionCode::DATASTRUCTURES_PAGE_PARENT_MUST_BE_IN_HIERARCHY->value);
        new Page(
            new Page(null, '_', '_', '_', isPartOfHierarchy: false),
            '_',
            '_',
            '_',
            isPartOfHierarchy: true,
        );
    }
}
