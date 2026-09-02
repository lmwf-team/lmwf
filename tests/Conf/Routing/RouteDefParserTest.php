<?php

declare(strict_types=1);

namespace LMWF\Tests\Conf\Routing;

use LMWF\Conf\RouteDefParser;
use LMWF\Http\DataStructures\RouteDef;
use LMWF\Conf\Http\SubRouteCannotAddRoleConfException;
use LMWF\Conf\Http\UnauthorizedAttributeConfException;
use LMWF\DataStructures\AppList;
use LMWF\DataStructures\AppPosIntArray;
use LMWF\DataStructures\AppObject;
use LMWF\DataStructures\Factory\CollectionFactory;
use LMWF\Http\DataStructures\PageConf;
use LMWF\ErrorHandling\ExceptionCode;
use LMWF\Http\DataStructures\EntPageConf;
use LMWF\Tests\Factory\PageParamFactory;
use LMWF\Tests\Mocks\Controller3;
use LMWF\Tests\Mocks\OkController;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;
use LMWF\Tests\Mocks\MockController;
use LMWF\Tests\Mocks\Controller2;
use LMWF\Tests\Mocks\UserRepo;

final class RouteDefParserTest extends TestCase
{
    const string BASE_URL = 'https://example.org';

    public function testBaseUrlInvalid(): void
    {
        $this->expectExceptionCode(ExceptionCode::CONF_ROUTEDEFPARSER_BASE_URL_MUST_NOT_HAVE_TRAILING_SLASH->value);
        new RouteDefParser('https://example.org/');
    }

    public function testParsingBasicConf(): void
    {
        $rootRouteDef = new RouteDef(null, null, ["ADMIN", "VISITOR"], children: [
            '' => new RouteDef(Controller3::class, PageConf::createStatic('Home', 'https://example.org', true, true), ["ADMIN", 'VISITOR']),
            'test' => new RouteDef(Controller2::class, PageParamFactory::create('Test Page', 'https://example.org', false, false), ["ADMIN", "VISITOR"]),
        ]);

        $actualRouteDef = $this->parseJson(__DIR__ . "/resources/route.json");

        self::assertEquals($rootRouteDef, $actualRouteDef);
    }

    public function testParsingWithPageMetatadaConfWithParams(): void
    {
        $expected = new RouteDef()
        new EntPageConf('{{ name }}', UserRepo::class);
        $routeConf = new AppObject([
            'fqcn' => OkController::class,
            'roles' => new AppList(),
            'page' => new AppObject([
                'fqcn' => OkController::class,
                'confByParam' => new AppPosIntArray([
                    0 => new AppObject([
                        'title' => 'Users',
                    ]),
                    1 => new AppObject([
                        'title' => '{{ name }}',
                        'repoFqcn' => UserRepo::class,
                    ])
                ]),
            ]),
            'maxArgs' => 1,
        ]);

        $routeDef = new RouteDefParser(self::BASE_URL)->parse($routeConf);

        self::assertEquals($expected, $routeDef);
    }

    public function testJsonThatAddsRoles(): void
    {
        $this->expectException(SubRouteCannotAddRoleConfException::class);
        $this->parseJson(__DIR__ . "/resources/added_role_in_sub_route.json");
    }

    public function testJsonWithBoth(): void
    {
        $expected = new RouteDef(
            null,
            null,
            ["ADMIN", "VISITOR"],
            children: [
                'sub' => new RouteDef(
                    MockController::class,
                    PageParamFactory::create('Sub Page', self::BASE_URL),
                    ["ADMIN"],
                    nParamsMin: 0,
                    nParamsMax: 3,
                ),
            ],
        );
        self::assertEquals($expected, $this->parseJson(__DIR__ . "/resources/route_w_both.json"));
    }

    public function testJsonWithExtra0(): void
    {
        $this->expectException(UnauthorizedAttributeConfException::class);
        $this->parseJson(__DIR__ . "/resources/route_w_extra_0.json");
    }

    public function testJsonWithExtra1(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->parseJson(__DIR__ . "/resources/route_w_extra_1.json");
    }

    public function testJsonWithExtra2(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->parseJson(__DIR__ . "/resources/route_w_extra_2.json");
    }

    public function testJsonWithEntConf(): void
    {
        $rootRouteDef = $this->parseJson(__DIR__ . '/resources/route_w_params_4.json');
        self::assertEquals(
            UserRepo::class,
            $rootRouteDef->pageParam?->entConf?->repoFqcn,
        );
    }

    public function testJsonWithParams(): void
    {
        $expected = new RouteDef(MockController::class, PageParamFactory::create(baseUrl: self::BASE_URL));
        self::assertEquals($expected, $this->parseJson(__DIR__ . "/resources/route_w_params_0.json"));
        self::assertEquals($expected, $this->parseJson(__DIR__ . "/resources/route_w_params_1.json"));
        self::assertEquals($expected, $this->parseJson(__DIR__ . "/resources/route_w_params_2.json"));

        $expected2 = new RouteDef(MockController::class, PageParamFactory::create(baseUrl: self::BASE_URL), ["VISITOR"], nParamsMin: 1, nParamsMax: 5);
        self::assertEquals($expected2, $this->parseJson(__DIR__ . "/resources/route_w_params_3.json"));
    }

    public function parseJson(string $filePath, bool $allowOverridingRoles = false): RouteDef
    {
        $jsonDecoded = CollectionFactory::createDeepAppObject(CollectionFactory::fromJson($filePath));
        $parser = new RouteDefParser(self::BASE_URL);
        return $parser->parse($jsonDecoded, allowOverridingParentRoles: $allowOverridingRoles);
    }
}
