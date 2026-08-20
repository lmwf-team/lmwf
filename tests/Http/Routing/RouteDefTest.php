<?php

declare(strict_types=1);

namespace LMWF\Tests\Http\Routing;

use LMWF\Conf\Http\RouteDef;
use LMWF\ErrorHandling\ExceptionCode;
use LMWF\Tests\Factory\PageParamFactory;
use LMWF\Tests\Mocks\ControllerMock;
use PHPUnit\Framework\TestCase;

final class RouteDefTest extends TestCase
{
    public function testWithInvalidRoles(): void
    {
        $this->expectExceptionCode(ExceptionCode::CONF_HTTP_ROUTEDEF_ROLE_IS_NOT_A_STRING->value);
        new RouteDef(null, null, roles: [1]);
    }

    public function testWithNoRouteId(): void
    {
        $this->expectExceptionCode(ExceptionCode::CONF_HTTP_ROUTEDEF_SUBROUTE_PATH_SEGMENT_IS_NOT_A_STRING->value);
        new RouteDef(null, null, subRouteDefs: [new RouteDef(self::class, PageParamFactory::create())]);
    }

    public function testWithNegativeMinArgs(): void
    {
        $this->expectExceptionCode(ExceptionCode::CONF_HTTP_ROUTEDEF_N_ARGS_LOWER_IS_NEG->value);
        new RouteDef(null, null, nArgsLowerLimit: -1);
    }

    public function testWithNegativeMaxArgs(): void
    {
        $this->expectExceptionCode(ExceptionCode::CONF_HTTP_ROUTEDEF_N_ARGS_UPPER_IS_BELOW_LOWER_IS_NEG->value);
        new RouteDef(null, null, nArgsUpperLimit: -1);
    }

    public function testWithMaxArgsLowerThanMinArgs(): void
    {
        $this->expectExceptionCode(ExceptionCode::CONF_HTTP_ROUTEDEF_N_ARGS_UPPER_IS_BELOW_LOWER_IS_NEG->value);
        new RouteDef(null, null, nArgsLowerLimit: 3, nArgsUpperLimit: 1);
    }

    /**
     * Expects RouteDef to throw an exception because page title must be null if
     * no FQCN is provided.
     */
    public function testWithPageTitle(): void
    {
        $this->expectExceptionCode(ExceptionCode::CONF_HTTP_ROUTEDEF_PAGEPARAM_IS_NOT_NULL->value);
        new RouteDef(null, PageParamFactory::create());
    }

    /**
     * Expects RouteDef to throw an exception because page title MUST NOT be
     * null if AT LEAST one FQCN is provided.
     */
    public function testWithNoPageTitle(): void
    {
        $this->expectExceptionCode(ExceptionCode::CONF_HTTP_ROUTEDEF_PAGEPARAM_IS_NULL->value);
        new RouteDef(ControllerMock::class, null);
    }
}
