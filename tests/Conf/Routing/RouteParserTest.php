<?php

declare(strict_types=1);

namespace LM\WebFramework\Tests\Conf\Routing;

use LM\WebFramework\Configuration\RouteDefParser;
use LM\WebFramework\Http\Routing\Exception\InvalidRouteConfException;
use LM\WebFramework\Http\Routing\Exception\OnlyChildCannotHaveSiblingsException;
use LM\WebFramework\Http\Routing\Exception\OnlyChildMustTakeAtLeastOneArgument;
use LM\WebFramework\Http\Routing\Exception\SubRouteCannotAddRoleConfException;
use LM\WebFramework\Http\Routing\Exception\UnauthorizedAttributeConfException;
use LM\WebFramework\Http\Routing\OnlyChildParentRouteDef;
use LM\WebFramework\Http\Routing\ParameterizedRoute;
use LM\WebFramework\Http\Routing\ParentRoute;
use LM\WebFramework\Http\Routing\RouteDef;
use PHPUnit\Framework\TestCase;
use TypeError;

final class RouteParserTest extends TestCase
{
    public function testAddingRoles(): void
    {
        $this->expectException(SubRouteCannotAddRoleConfException::class);
        $this->parseJson(__DIR__ . "/resources/added_role_in_sub_route.json");
    }

    public function testParsing(): void
    {
        $expected = new ParentRoute(
            "App\Controller\RouteController",
            roles: ["ADMIN", "VISITOR"],
            routes: [
                'test' => new ParentRoute(
                    "App\Controller\TestController",
                    ["ADMIN", "VISITOR"],
                ),
            ],
        );
        $actualRoute = $this->parseJson(__DIR__ . "/resources/route.json");
        $this->assertEquals($expected, $actualRoute);
    }

    public function testParsingWithParams(): void
    {
        $expected = new ParameterizedRoute("Controller");
        $this->assertEquals($expected, $this->parseJson(__DIR__ . "/resources/route_w_params_0.json"));
        $this->assertEquals($expected, $this->parseJson(__DIR__ . "/resources/route_w_params_1.json"));
        $this->assertEquals($expected, $this->parseJson(__DIR__ . "/resources/route_w_params_2.json"));

        $expected2 = new ParameterizedRoute("Controller", roles: ["VISITOR"], minArgs: 1, maxArgs: 5);
        $this->assertEquals($expected2, $this->parseJson(__DIR__ . "/resources/route_w_params_3.json"));
    }

    public function testParsingWithBoth(): void
    {
        $expected = new ParentRoute(
            "Controller",
            ["ADMIN", "VISITOR"],
            routes: [
                'sub' => new ParameterizedRoute("Controller", roles: ["ADMIN"], minArgs: 0, maxArgs: 3),
            ],
        );
        $this->assertEquals($expected, $this->parseJson(__DIR__ . "/resources/route_w_both.json"));
    }

    public function testParsingInvalidOnlyChild0(): void
    {
        $this->expectException(OnlyChildCannotHaveSiblingsException::class);
        $this->parseJson(__DIR__ . "/resources/invalid_only_child_0.json");
    }

    public function testParsingInvalidOnlyChild1(): void
    {
        $this->expectException(OnlyChildMustTakeAtLeastOneArgument::class);
        $this->parseJson(__DIR__ . "/resources/invalid_only_child_1.json");
    }

    public function testParsingInvalidRoute(): void
    {
        $this->expectException(InvalidRouteConfException::class);
        $this->parseJson(__DIR__ . "/resources/route_invalid.json");
    }

    public function testParsingRouteWithExtra0(): void
    {
        $this->expectException(UnauthorizedAttributeConfException::class);
        $this->parseJson(__DIR__ . "/resources/route_w_extra_0.json");
    }

    public function testParsingRouteWithExtra1(): void
    {
        $this->expectException(TypeError::class);
        $this->parseJson(__DIR__ . "/resources/route_w_extra_1.json");
    }

    public function testParsingRouteWithExtra2(): void
    {
        $this->expectException(UnauthorizedAttributeConfException::class);
        $this->parseJson(__DIR__ . "/resources/route_w_extra_2.json");
    }

    public function testParsingOnlyChildParent(): void
    {
        $expected = new OnlyChildParentRouteDef(
            "App\Controller\RouteController",
            new ParameterizedRoute("App\Controller\SubRouteController", minArgs: 1, maxArgs: 1),
        );
        $routeDef = $this->parseJson(__DIR__ . "/resources/route_w_only_child.json");
        $this->assertEquals($expected, $routeDef);
    }

    public function parseJson(string $filePath, bool $allowOverridingRoles = false): RouteDef
    {
        $jsonDecoded = json_decode(
            file_get_contents($filePath),
            associative: true,
            flags: JSON_THROW_ON_ERROR,
        );
        $parser = new RouteDefParser();
        return $parser->parse($jsonDecoded, allowOverridingParentRoles: $allowOverridingRoles);
    }
}
