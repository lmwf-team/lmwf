<?php

declare(strict_types=1);

namespace LM\WebFramework\Tests\Http\Routing;

use DomainException;
use LM\WebFramework\Http\Routing\Router;
use PHPUnit\Framework\TestCase;

final class PathSegmenterTest extends TestCase
{
    public function testPathSegmentation(): void
    {
        $router = new Router();

        $this->assertSame(['', '', ], $router->getSegs('/'));
        $this->assertSame(['', '', ], $router->getSegs(''));
        $this->assertSame(['', '', ''], $router->getSegs('//'));
        $this->assertSame(['', '', '', ''], $router->getSegs('///'));
        $this->assertSame(['', 'test'], $router->getSegs('/test'));
        $this->assertSame(['', 'test', 'sub'], $router->getSegs('/test/sub'));
        $this->assertSame(['', 'test', 'sub', ''], $router->getSegs('/test/sub/'));
    }

    public function testRelativePaths(): void
    {
        $router = new Router();
        $this->expectException(DomainException::class);
        $router->getSegs('relative/url');
    }

    public function testRelativePaths2(): void
    {
        $router = new Router();
        $this->expectException(DomainException::class);
        $router->getSegs('test');
    }
}
