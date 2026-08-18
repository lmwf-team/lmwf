<?php

declare(strict_types=1);

namespace LMWF\Tests\Factory;

use LMWF\DataStructures\Page;

final readonly class PageFactory
{
    public static function createPage(?Page $parent = null): Page
    {
        return new Page($parent, '', '');
    }
}