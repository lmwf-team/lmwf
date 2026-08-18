<?php

declare(strict_types=1);

namespace LMWF\Tests\Conf\Routing;

use LMWF\Conf\RouteDefParser;
use LMWF\Conf\Http\RouteDef;
use LMWF\Conf\Http\SubrouteCannotAddRoleConfException;
use LMWF\Conf\Http\UnauthorizedAttributeConfException;
use LMWF\DataStructures\Factory\CollectionFactory;
use LMWF\DataStructures\PageParam;
use LMWF\ErrorHandling\ExceptionCode;
use LMWF\Tests\Factory\PageParamFactory;
use LMWF\Tests\Mocks\ControllerMock;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;
use LMWF\Tests\Mocks\RoutedController;
use LMWF\Tests\Mocks\TestController;

final class RouteDefParserTest extends TestCase
{
    const string BASE_URL = 'https://example.org';

    public function testBaseUrlInvalid(): void
    {
        $this->expectExceptionCode(ExceptionCode::CONF_ROUTEDEFPARSER_BASE_URL_MUST_NOT_HAVE_TRAILING_SLASH->value);
        new RouteDefParser('https://example.org/');
    }

    public function testAddingRoles(): void
    {
        $this->expectException(SubrouteCannotAddRoleConfException::class);
        $this->parseJson(__DIR__ . "/resources/added_role_in_sub_route.json");
    }

    public function testParsingBasicConf(): void
    {
        $rootRouteDef = new RouteDef(null, null, ["ADMIN", "VISITOR"], subroutes: [
            '' => new RouteDef(ControllerMock::class, new PageParam('Home', 'https://example.org', true, true), ["ADMIN", 'VISITOR']),
            'test' => new RouteDef(TestController::class, PageParamFactory::create('Test Page', 'https://example.org', false, false), ["ADMIN", "VISITOR"]),
        ]);

        $actualRouteDef = $this->parseJson(__DIR__ . "/resources/route.json");

        self::assertEquals($rootRouteDef, $actualRouteDef);
    }

    public function testParsingWithParams(): void
    {
        $expected = new RouteDef(RoutedController::class, PageParamFactory::create(baseUrl: self::BASE_URL));
        self::assertEquals($expected, $this->parseJson(__DIR__ . "/resources/route_w_params_0.json"));
        self::assertEquals($expected, $this->parseJson(__DIR__ . "/resources/route_w_params_1.json"));
        self::assertEquals($expected, $this->parseJson(__DIR__ . "/resources/route_w_params_2.json"));

        $expected2 = new RouteDef(RoutedController::class, PageParamFactory::create(baseUrl: self::BASE_URL), ["VISITOR"], nArgsLowerLimit: 1, nArgsUpperLimit: 5);
        self::assertEquals($expected2, $this->parseJson(__DIR__ . "/resources/route_w_params_3.json"));
    }

    public function testParsingWithBoth(): void
    {
        $expected = new RouteDef(
            null,
            null,
            ["ADMIN", "VISITOR"],
            subroutes: [
                'sub' => new RouteDef(
                    RoutedController::class,
                    PageParamFactory::create('Sub Page', self::BASE_URL),
                    ["ADMIN"],
                    nArgsLowerLimit: 0,
                    nArgsUpperLimit: 3,
                ),
            ],
        );
        self::assertEquals($expected, $this->parseJson(__DIR__ . "/resources/route_w_both.json"));
    }

    public function testParsingRouteWithExtra0(): void
    {
        $this->expectException(UnauthorizedAttributeConfException::class);
        $this->parseJson(__DIR__ . "/resources/route_w_extra_0.json");
    }

    public function testParsingRouteWithExtra1(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->parseJson(__DIR__ . "/resources/route_w_extra_1.json");
    }

    public function testParsingRouteWithExtra2(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->parseJson(__DIR__ . "/resources/route_w_extra_2.json");
    }

    public function parseJson(string $filePath, bool $allowOverridingRoles = false): RouteDef
    {
        $jsonDecoded = CollectionFactory::createDeepAppObject(CollectionFactory::fromJson($filePath));
        $parser = new RouteDefParser(self::BASE_URL);
        return $parser->parse($jsonDecoded, allowOverridingParentRoles: $allowOverridingRoles);
    }
}
