<?php

declare(strict_types=1);

namespace LM\WebFramework\Tests\Http\Routing;

use LM\WebFramework\Http\Routing\Exception\InvalidRouteConfException;
use LM\WebFramework\Http\Routing\ParameterizedRoute;
use LM\WebFramework\Http\Routing\ParentRoute;
use LM\WebFramework\Http\Routing\Route;
use LM\WebFramework\Http\Routing\RouteParser;
use PHPUnit\Framework\TestCase;

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
}