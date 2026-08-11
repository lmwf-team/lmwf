<?php

declare(strict_types=1);

namespace LM\WebFramework\Tests\Http\Routing;

use InvalidArgumentException;
use LM\WebFramework\Http\Routing\ParameterizedRoute;
use LM\WebFramework\Http\Routing\ParentRoute;
use PHPUnit\Framework\TestCase;

final class RouteTest extends TestCase
{
    public function testWithInvalidRoles(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ParentRoute(self::class, roles: [1]);
    }

    public function testWithNoRouteId(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ParentRoute(self::class, routes: [new ParentRoute(self::class)]);
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

    public function testWithNegativeMinArgs(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ParameterizedRoute(self::class, minArgs: -1);
    }

    public function testWithNegativeMaxArgs(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ParameterizedRoute(self::class, maxArgs: -1);
    }

    public function testWithMaxArgsLowerThanMinArgs(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ParameterizedRoute(self::class, minArgs:3, maxArgs: 1);
    }
}