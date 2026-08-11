<?php

declare(strict_types=1);

namespace LM\WebFramework\Tests\Conf\Routing;

use LM\WebFramework\Configuration\RouteDefParser;
use LM\WebFramework\Http\Routing\Exception\InvalidRouteConfException;
use LM\WebFramework\Http\Routing\Exception\SubrouteCannotAddRoleConfException;
use LM\WebFramework\Http\Routing\Exception\UnauthorizedAttributeConfException;
use LM\WebFramework\Http\Routing\RouteDef;
use PHPUnit\Framework\TestCase;
use TypeError;

final class RouteDefParserTest extends TestCase
{
    public function testAddingRoles(): void
    {
        $this->expectException(SubrouteCannotAddRoleConfException::class);
        $this->parseJson(__DIR__ . "/resources/added_role_in_sub_route.json");
    }

    public function testParsing(): void
    {
        $homeRouteDef = new RouteDef('HomeController', ["ADMIN", 'VISITOR']);
        $testRouteDef = new RouteDef("TestController", ["ADMIN", "VISITOR"]);
        $rootRouteDef = new RouteDef(null, ["ADMIN", "VISITOR"], subroutes: [
            '' => $homeRouteDef,
            'test' => $testRouteDef,
        ]);
        $actualRouteDef = $this->parseJson(__DIR__ . "/resources/route.json");
        $this->assertEquals($rootRouteDef, $actualRouteDef);
    }

    public function testParsingWithParams(): void
    {
        $expected = new RouteDef('Controller');
        $this->assertEquals($expected, $this->parseJson(__DIR__ . "/resources/route_w_params_0.json"));
        $this->assertEquals($expected, $this->parseJson(__DIR__ . "/resources/route_w_params_1.json"));
        $this->assertEquals($expected, $this->parseJson(__DIR__ . "/resources/route_w_params_2.json"));

        $expected2 = new RouteDef("Controller", ["VISITOR"], nArgsLowerLimit: 1, nArgsUpperLimit: 5);
        $this->assertEquals($expected2, $this->parseJson(__DIR__ . "/resources/route_w_params_3.json"));
    }

    public function testParsingWithBoth(): void
    {
        $expected = new RouteDef(
            null,
            ["ADMIN", "VISITOR"],
            subroutes: [
                'sub' => new RouteDef("Controller", ["ADMIN"], nArgsLowerLimit: 0, nArgsUpperLimit: 3),
            ],
        );
        $this->assertEquals($expected, $this->parseJson(__DIR__ . "/resources/route_w_both.json"));
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
