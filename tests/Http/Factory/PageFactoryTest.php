<?php

declare(strict_types=1);

namespace LMWF\Tests\Http\Factory;

use LMWF\DataStructures\Page;
use LMWF\Http\DataStructures\EntPageConf;
use LMWF\Http\DataStructures\RouteDef;
use LMWF\Http\Factory\PageFactory;
use LMWF\Http\Routing\EntPageTitleFormatter;
use LMWF\Http\Routing\Route;
use LMWF\Tests\Factory\RouteFactory;
use LMWF\Tests\Mocks\ContainerMock;
use LMWF\Tests\Mocks\UnderscoreController;
use LMWF\Tests\Mocks\UserRepo;
use PHPUnit\Framework\TestCase;

final class PageFactoryTest extends TestCase
{
    const string BASE_URL = 'http://localhost';

    private PageFactory $factory;

    #[\Override]
    public function setUp(): void
    {
        $this->factory = new PageFactory(new EntPageTitleFormatter(new ContainerMock([
            UserRepo::class => new UserRepo(),
        ])));
    }

    public function testCreation(): void
    {
        $expectedFormattedStrings = [
            '{{ name }}' => UserRepo::USER_NAME,
            '{{ id }}' => UserRepo::USER_ID,
            '{{ a }}' => UserRepo::USER_A,
        ];
        foreach ($expectedFormattedStrings as $format => $output) {
            $entConf = new EntPageConf(
                UserRepo::class,
                $format,
                UnderscoreController::class,
                self::BASE_URL,
                indexed: true,
                inHierarchy: true,
            );
            $rootRoute = RouteFactory::createRootRoute([
                '_' => $def = new RouteDef(null, [$entConf]),
            ]);
            $expectedPage = new Page(
                null,
                UnderscoreController::class,
                $output,
                self::BASE_URL . '/_/' . UserRepo::USER_ID,
                isIndexed: true,
                isPartOfHierarchy: true,
            );

            self::assertEquals($expectedPage, $this->factory->create(new Route(
                $def,
                parent: $rootRoute,
                seg: '_',
                params: [UserRepo::USER_ID],
            )));
        }
    }

    public function testWithWildCard(): void
    {
        $entConf = new EntPageConf(
            UserRepo::class,
            '{{ name }}',
            UnderscoreController::class,
            self::BASE_URL,
            indexed: true,
            inHierarchy: true,
        );
        $rootRoute = RouteFactory::createRootRoute([
            '_' => $parentDef = new RouteDef(children: [
                '*' => $def = new RouteDef($entConf),
            ]),
        ]);
        $parentRoute = new Route($parentDef, $rootRoute, '_');

        $expectedPage = new Page(
            null,
            UnderscoreController::class,
            UserRepo::USER_NAME,
            self::BASE_URL . '/_/' . UserRepo::USER_ID,
            isIndexed: true,
            isPartOfHierarchy: true,
        );
        self::assertEquals($expectedPage, $this->factory->create(new Route(
            $def,
            parent: $parentRoute,
            seg: UserRepo::USER_ID,
        )));
    }
}
