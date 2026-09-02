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
use LMWF\Http\DataStructures\StaticPageConf;
use LMWF\Http\Routing\Route;
use LMWF\Tests\Factory\PageParamFactory;
use LMWF\Tests\Mocks\Controller1;
use LMWF\Tests\Mocks\Controller3;
use LMWF\Tests\Mocks\OkController;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;
use LMWF\Tests\Mocks\MockController;
use LMWF\Tests\Mocks\Controller2;
use LMWF\Tests\Mocks\UserRepo;

final class RouteDefParserTest extends TestCase
{
    const string BASE_URL = '_';

    public function testBaseUrlInvalid(): void
    {
        $this->expectExceptionCode(ExceptionCode::CONF_ROUTEDEFPARSER_BASE_URL_MUST_NOT_HAVE_TRAILING_SLASH->value);
        new RouteDefParser('https://example.org/');
    }

    // @todo test defaults of isIndexed and isInHierarchy, and defaults based on ancestors
    // @todo test either it’s page or it's params
    // @todo test exception (?) if route adds role
    public function testParsingValidConf(): void
    {
        $expectedDef = new RouteDef(
            new StaticPageConf('Home', Controller1::class, self::BASE_URL, isIndexed: true, isInHierarchy: true),
            roles: ["ADMIN", "VISITOR"],
            children: [
                'profile' => new RouteDef(
                    noParamConf: null,
                    roles: ['ADMIN', 'VISITOR'],
                    params: [
                        0 => new EntPageConf(UserRepo::class, 'Hi {{ name }}', Controller2::class, self::BASE_URL, indexed: false, inHierarchy: true),
                    ],
                ),
                'admin' => new RouteDef(
                    roles: ['ADMIN'],
                    children: [
                        'logout' => new RouteDef(
                            new StaticPageConf('Logout', Controller3::class, self::BASE_URL, isIndexed: false, isInHierarchy: false),
                            roles: ['ADMIN'],
                        )
                    ]
                )
            ],
        );

        $actualRouteDef = $this->parseArray([
            'page' => [
                0 => [
                    'title' => 'Home',
                    'fqcn' => Controller1::class,
                ]
            ],
            'roles' => ['ADMIN', 'VISITOR'],
            'routes' => [
                'profile' => [
                    'indexed' => false,
                    'inHierarchy' => true,
                    'page' => [
                        0 => null,
                        1 => [
                            'type' => 'ent',
                            'title' => 'Hi {{ name }}',
                            'fqcn' => Controller2::class,
                            'repoFqcn' => UserRepo::class
                        ]
                    ]
                ],
                'admin' => [
                    'indexed' => false,
                    'inHierarchy' => false,
                    'roles' => ['ADMIN'],
                    'routes' => [
                        'logout' => [
                            'page' => [
                                [
                                    'title' => 'Logout',
                                    'fqcn' => Controller3::class,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        self::assertEquals($expectedDef, $actualRouteDef);
    }

    public function testAddingRoles(): void
    {
        $this->expectException(SubRouteCannotAddRoleConfException::class);
        $this->parseArray([
            'roles' => ['ADMIN'],
            'routes' => [
                '_' => [
                    'roles' => ['ADMIN', 'VISITOR'],
                ],
            ],
        ]);
    }

    public function testUndefinedProperty(): void
    {
        $this->expectException(UnauthorizedAttributeConfException::class);
        $this->parseArray([
            'extra' => true,
        ]);
    }

    public function testInvalidDefData(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->parseArray([
            'roles' => [],
            'routes' => [
                '_' => [],
                '__' => 0,
            ],
        ]);
    }

    public function testUndefinedProperty2(): void
    {
        $this->expectException(UnauthorizedAttributeConfException::class);
        $this->parseArray([
            'roles' => [],
            'routes' => [
                '_' => [
                    'extra' => true
                ],
            ],
        ]);
    }

    private function parseArray(array $defData): RouteDef
    {
        return new RouteDefParser(self::BASE_URL)->parse(CollectionFactory::createDeepAppObject($defData));
    }
}
