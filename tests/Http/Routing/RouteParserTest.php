<?php

declare(strict_types=1);

namespace LM\WebFramework\Tests\Http\Routing;

use LM\WebFramework\Http\Routing\Exception\InvalidRouteConfException;
use LM\WebFramework\Http\Routing\Exception\OnlyChildCannotHaveSiblingsException;
use LM\WebFramework\Http\Routing\Exception\OnlyChildMustTakeAtLeastOneArgument;
use LM\WebFramework\Http\Routing\Exception\SubRouteCannotAddRoleConfException;
use LM\WebFramework\Http\Routing\Exception\UnauthorizedAttributeConfException;
use LM\WebFramework\Http\Routing\OnlyChildParentRouteDef;
use LM\WebFramework\Http\Routing\ParameterizedRoute;
use LM\WebFramework\Http\Routing\ParentRoute;
use LM\WebFramework\Http\Routing\RouteDef;
use LM\WebFramework\Http\Routing\RouteDefParser;
use PHPUnit\Framework\TestCase;
use TypeError;

final class RouteParserTest extends TestCase
{
    public function testAddingRoles(): void
    {
        $this->expectException(SubRouteCannotAddRoleConfException::class);
        $parser = new RouteDefParser();
        $actualRoute = $parser->parseJson(__DIR__ . "/resources/added_role_in_sub_route.json");
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
        $parser = new RouteDefParser();
        $actualRoute = $parser->parseJson(__DIR__ . "/resources/route.json");
        $this->assertEquals($expected, $actualRoute);
    }

    public function testParsingWithParams(): void
    {
        $expected = new ParameterizedRoute("Controller");
        $parser = new RouteDefParser();
        $this->assertEquals($expected, $parser->parseJson(__DIR__ . "/resources/route_w_params_0.json"));
        $this->assertEquals($expected, $parser->parseJson(__DIR__ . "/resources/route_w_params_1.json"));
        $this->assertEquals($expected, $parser->parseJson(__DIR__ . "/resources/route_w_params_2.json"));

        $expected2 = new ParameterizedRoute("Controller", roles: ["VISITOR"], minArgs: 1, maxArgs: 5);
        $this->assertEquals($expected2, $parser->parseJson(__DIR__ . "/resources/route_w_params_3.json"));
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
        $parser = new RouteDefParser();
        $this->assertEquals($expected, $parser->parseJson(__DIR__ . "/resources/route_w_both.json"));
    }

    public function testParsingInvalidOnlyChild0(): void
    {
        $parser = new RouteDefParser();
        $this->expectException(OnlyChildCannotHaveSiblingsException::class);
        $parser->parseJson(__DIR__ . "/resources/invalid_only_child_0.json");
    }

    public function testParsingInvalidOnlyChild1(): void
    {
        $parser = new RouteDefParser();
        $this->expectException(OnlyChildMustTakeAtLeastOneArgument::class);
        $parser->parseJson(__DIR__ . "/resources/invalid_only_child_1.json");
    }

    public function testParsingInvalidRoute(): void
    {
        $parser = new RouteDefParser();
        $this->expectException(InvalidRouteConfException::class);
        $parser->parseJson(__DIR__ . "/resources/route_invalid.json");
    }

    public function testParsingRouteWithExtra0(): void
    {
        $parser = new RouteDefParser();
        $this->expectException(UnauthorizedAttributeConfException::class);
        $parser->parseJson(__DIR__ . "/resources/route_w_extra_0.json");
    }

    public function testParsingRouteWithExtra1(): void
    {
        $parser = new RouteDefParser();
        $this->expectException(TypeError::class);
        $parser->parseJson(__DIR__ . "/resources/route_w_extra_1.json");
    }

    public function testParsingRouteWithExtra2(): void
    {
        $parser = new RouteDefParser();
        $this->expectException(UnauthorizedAttributeConfException::class);
        $parser->parseJson(__DIR__ . "/resources/route_w_extra_2.json");
    }

    public function testParsingOnlyChildParent(): void
    {
        $expected = new OnlyChildParentRouteDef(
            "App\Controller\RouteController",
            new ParameterizedRoute("App\Controller\SubRouteController", minArgs: 1, maxArgs: 1),
        );
        $parser = new RouteDefParser();
        $routeDef = $parser->parseJson(__DIR__ . "/resources/route_w_only_child.json");
        $this->assertEquals($expected, $routeDef);
    }
}
