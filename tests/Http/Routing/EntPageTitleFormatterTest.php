<?php

declare(strict_types=1);

namespace LMWF\Tests\Http\Routing;

use LMWF\Http\DataStructures\EntPageConf;
use LMWF\Http\Routing\EntPageTitleFormatter;
use LMWF\Tests\Mocks\ContainerMock;
use LMWF\Tests\Mocks\UserRepo;
use PHPUnit\Framework\TestCase;

final class EntPageTitleFormatterTest extends TestCase
{
    private EntPageTitleFormatter $formatter;

    #[\Override]
    public function setUp(): void
    {
        $this->formatter = new EntPageTitleFormatter(new ContainerMock([
            UserRepo::class => new UserRepo(),
        ]));
    }

    public function testEmptyStr(): void
    {
        self::assertEquals('', $this->formatter->format(new EntPageConf('', UserRepo::class), UserRepo::USER_ID));
    }

    public function testStaticStr(): void
    {
        self::assertEquals('Hello!', $this->formatter->format(new EntPageConf('Hello!', UserRepo::class), UserRepo::USER_ID));
    }

    public function testOneParam(): void
    {
        self::assertEquals(
            'Hello ' . UserRepo::USER_NAME . '!',
            $this->formatter->format(new EntPageConf('Hello {{ name }}!', UserRepo::class), UserRepo::USER_ID),
        );
    }
}
