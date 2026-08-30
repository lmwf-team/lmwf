<?php

declare(strict_types=1);

namespace LMWF\Tests\Http\Routing;

use LMWF\Conf\Http\RouteDef;
use LMWF\ErrorHandling\ExceptionCode;
use LMWF\Http\DataStructures\StaticPageConf;
use LMWF\Tests\Factory\PageParamFactory;
use LMWF\Tests\Mocks\Controller3;
use PHPUnit\Framework\TestCase;

final class RouteDefTest extends TestCase
{
    /**
     * Instantiation tests.
     */

    // @todo Check that pageConfs cannot have InheritedPageConf associated with
    // 0 or with all its parents null.

    // @todo Check with InheritedPageConf
    // @todo Check with invalid types in pageConfs

    public function testPageConfWithOutOfBoundsKeys(): void
    {
        $this->expectExceptionCode(ExceptionCode::CONF_HTTP_ROUTEDEF_PAGE_CONF_KEY_OUT_OF_BOUNDS->value);
        new RouteDef([
            -1 => PageParamFactory::createStaticConf(),
        ]);
    }

    public function testPageConfWithInvalidKeys(): void
    {
        $this->expectExceptionCode(ExceptionCode::CONF_HTTP_ROUTEDEF_PAGE_CONF_KEY_NOT_INT->value);
        new RouteDef([
            '_' => PageParamFactory::createStaticConf(),
        ]);
    }

    public function testPageConfIncomplete(): void
    {
        $this->expectExceptionCode(ExceptionCode::CONF_HTTP_ROUTEDEF_PAGE_CONF_MISSING->value);
        new RouteDef([]);
    }

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
        new RouteDef(null, null, nParamsLeast: -1);
    }

    public function testWithNegativeMaxArgs(): void
    {
        $this->expectExceptionCode(ExceptionCode::CONF_HTTP_ROUTEDEF_N_ARGS_UPPER_IS_BELOW_LOWER_IS_NEG->value);
        new RouteDef(null, null, nParamsMax: -1);
    }

    public function testWithMaxArgsLowerThanMinArgs(): void
    {
        $this->expectExceptionCode(ExceptionCode::CONF_HTTP_ROUTEDEF_N_ARGS_UPPER_IS_BELOW_LOWER_IS_NEG->value);
        new RouteDef(null, null, nParamsLeast: 3, nParamsMax: 1);
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
        new RouteDef(Controller3::class, null);
    }
}
