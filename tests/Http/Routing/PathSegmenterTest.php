<?php

declare(strict_types=1);

namespace LMWF\Tests\Http\Routing;

use DomainException;
use LMWF\Http\Routing\Router;
use PHPUnit\Framework\TestCase;

final class PathSegmenterTest extends TestCase
{
    public function testPathSegmentation(): void
    {
        $router = new Router();

        self::assertSame([''], $router->splitPathInSegs('/'));
        self::assertSame([''], $router->splitPathInSegs(''));
        self::assertSame(['', '', ''], $router->splitPathInSegs('//'));
        self::assertSame(['', '', '', ''], $router->splitPathInSegs('///'));
        self::assertSame(['', 'test'], $router->splitPathInSegs('/test'));
        self::assertSame(['', 'test', 'sub'], $router->splitPathInSegs('/test/sub'));
        self::assertSame(['', 'test', 'sub', ''], $router->splitPathInSegs('/test/sub/'));
    }

    public function testRelativePaths(): void
    {
        $router = new Router();
        $this->expectException(DomainException::class);
        $router->splitPathInSegs('relative/url');
    }

    public function testRelativePaths2(): void
    {
        $router = new Router();
        $this->expectException(DomainException::class);
        $router->splitPathInSegs('test');
    }
}
