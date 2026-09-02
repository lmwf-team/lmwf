<?php

declare(strict_types=1);

namespace LMWF\Tests\Http\Routing;

use LMWF\Http\DataStructures\RouteDef;
use LMWF\ErrorHandling\ExceptionCode;
use LMWF\Http\DataStructures\InheritedPageConf;
use LMWF\Http\DataStructures\StaticPageConf;
use LMWF\Tests\Factory\PageParamFactory;
use LMWF\Tests\Mocks\Controller3;
use PHPUnit\Framework\TestCase;

final class RouteDefTest extends TestCase
{
    /**
     * Instantiation tests.
     */

    public function testPageConfWithOutOfBoundsKeys(): void
    {
        $this->expectExceptionCode(ExceptionCode::HTTP_DATASTRUCTURES_ROUTEDEF_PAGE_CONF_KEY_OUT_OF_BOUNDS->value);
        new RouteDef( params: [
            -1 => PageParamFactory::createStaticConf(),
        ]);
    }

    public function testPageConfWithInvalidKeys(): void
    {
        $this->expectExceptionCode(ExceptionCode::HTTP_DATASTRUCTURES_ROUTEDEF_PAGE_CONF_KEY_OUT_OF_BOUNDS->value);
        new RouteDef(null, params: [
            '_' => PageParamFactory::createStaticConf(),
        ]);
    }

    public function testPageConfWithInvalidValues(): void
    {
        $this->expectExceptionCode(ExceptionCode::HTTP_DATASTRUCTURES_ROUTEDEF_PAGE_CONF_VALUE_INVALID_TYPE->value);
        new RouteDef( params: [true]);
    }

    public function testWithInvalidRoles(): void
    {
        $this->expectExceptionCode(ExceptionCode::HTTP_DATASTRUCTURES_ROUTEDEF_ROLE_IS_NOT_A_STRING->value);
        new RouteDef( roles: [1]);
    }

    public function testSubroutesWithNoRouteId(): void
    {
        $this->expectExceptionCode(ExceptionCode::HTTP_DATASTRUCTURES_ROUTEDEF_SUBROUTE_PATH_SEGMENT_IS_NOT_A_STRING->value);
        new RouteDef( children: [new RouteDef(noParamConf: null)]);
    }
}
