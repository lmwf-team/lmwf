<?php

declare(strict_types=1);

namespace LM\WebFramework\Tests\Http\Routing;

use LM\WebFramework\Http\Routing\Exception\InvalidRouteConfException;
use LM\WebFramework\Http\Routing\Exception\UnauthorizedAttributeConfException;
use LM\WebFramework\Http\Routing\ParameterizedRoute;
use LM\WebFramework\Http\Routing\ParentRoute;
use LM\WebFramework\Http\Routing\Route;
use LM\WebFramework\Http\Routing\RouteParser;
use PHPUnit\Framework\TestCase;
use TypeError;

final class RouteParserTest extends TestCase
{
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
        $parser = new RouteParser;
        $actualRoute = $parser->parseJson(__DIR__ . "/resources/route.json");
        $this->assertEquals($expected, $actualRoute);
    }

    public function testParsingWithParams(): void
    {
        $expected = new ParameterizedRoute("Controller");
        $parser = new RouteParser;
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
            ["VISITOR"],
            routes: [
                'sub' => new ParameterizedRoute("Controller", roles: ["ADMIN"], minArgs: 0, maxArgs: 3),
            ],
        );
        $parser = new RouteParser;
        $this->assertEquals($expected, $parser->parseJson(__DIR__ . "/resources/route_w_both.json"));
    }

    public function testParsingInvalidRoute(): void
    {
        $parser = new RouteParser;
        $this->expectException(InvalidRouteConfException::class);
        $parser->parseJson(__DIR__ . "/resources/route_invalid.json");
    }

    public function testParsingRouteWithExtra0(): void
    {
        $parser = new RouteParser;
        $this->expectException(UnauthorizedAttributeConfException::class);
        $parser->parseJson(__DIR__ . "/resources/route_w_extra_0.json");
    }

    public function testParsingRouteWithExtra1(): void
    {
        $parser = new RouteParser;
        $this->expectException(TypeError::class);
        $parser->parseJson(__DIR__ . "/resources/route_w_extra_1.json");
    }

    public function testParsingRouteWithExtra2(): void
    {
        $parser = new RouteParser;
        $this->expectException(UnauthorizedAttributeConfException::class);
        $parser->parseJson(__DIR__ . "/resources/route_w_extra_2.json");
    }
}