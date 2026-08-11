<?php

declare(strict_types=1);

namespace LM\WebFramework\Tests\Http\Routing;

use InvalidArgumentException;
use LM\WebFramework\Http\Routing\ParentRoute;
use PHPUnit\Framework\TestCase;

final class RouteTest extends TestCase
{
    public function testWithNoRouteId(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ParentRoute(self::class, routes: [new ParentRoute(self::class)]);
    }

    public function testWithInvalidRoles(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ParentRoute(self::class, roles: [1]);
    }

    public function testWithEmptyRouteId(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ParentRoute(self::class, routes: ['' => new ParentRoute(self::class)]);
    }

    public function testWithSlashInRouteId(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ParentRoute(self::class, routes: ['/' => new ParentRoute(self::class)]);
    }
}