<?php

declare(strict_types=1);

namespace LM\WebFramework\Tests\Http\Routing;

use InvalidArgumentException;
use LM\WebFramework\Http\Routing\RouteDef;
use LM\WebFramework\Http\Routing\RouteConf\ParentRouteConf;
use LM\WebFramework\Http\Routing\RouteConf\ParamRouteConf;
use PHPUnit\Framework\TestCase;

final class RouteDefTest extends TestCase
{
    public function testWithInvalidRoles(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new RouteDef(self::class, roles: [1]);
    }

    public function testWithNoRouteId(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ParentRouteConf([new RouteDef(self::class)]);
    }

    public function testWithNegativeMinArgs(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ParamRouteConf(-1);
    }

    public function testWithNegativeMaxArgs(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ParamRouteConf(nArgsUpperLimit: -1);
    }

    public function testWithMaxArgsLowerThanMinArgs(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ParamRouteConf(nArgsLowerLimit: 3, nArgsUpperLimit: 1);
    }
}
