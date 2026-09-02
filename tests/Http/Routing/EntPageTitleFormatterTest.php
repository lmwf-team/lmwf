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
        self::assertEquals('', $this->formatter->format('', UserRepo::USER_ID, UserRepo::class));
    }

    public function testStaticStr(): void
    {
        self::assertEquals('Hello!', $this->formatter->format('Hello!', UserRepo::USER_ID, UserRepo::class));
    }

    public function testOneParam(): void
    {
        self::assertEquals(
            'Hello ' . UserRepo::USER_NAME . '!',
            $this->formatter->format('Hello {{ name }}!', UserRepo::USER_ID, UserRepo::class),
        );
    }

    public function testOneParamTwice(): void
    {
        self::assertEquals(
            UserRepo::USER_NAME . ' = ' . UserRepo::USER_NAME,
            $this->formatter->format('{{ name }} = {{ name }}', UserRepo::USER_ID, UserRepo::class),
        );
    }
}
