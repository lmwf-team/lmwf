<?php

namespace LM\WebFramework\Tests;

use DateTimeImmutable;
use LM\WebFramework\Database\DbEntityManager;
use LM\WebFramework\Database\Exceptions\InvalidDbDataException;
use LM\WebFramework\Database\Exceptions\NullDbDataNotAllowedException;
use LM\WebFramework\Model\BoolModel;
use LM\WebFramework\Model\DateTimeModel;
use LM\WebFramework\Model\IntegerModel;
use LM\WebFramework\Model\UintModel;
use PHPUnit\Framework\TestCase;

class DbEntityManagerTest extends TestCase
{
    private DbEntityManager $em;

    public function setUp(): void
    {
        $this->em = new DbEntityManager();
    }

    public function testScalarTypes(): void
    {
        

        $this->assertTrue($this->em->toAppData(1, new BoolModel()));
        $this->assertFalse($this->em->toAppData(0, new BoolModel()));
        $this->assertNull($this->em->toAppData(null, new BoolModel(isNullable: true)));

        $this->assertEquals(47, $this->em->toAppData(47, new IntegerModel()));

        $date = '2024-07-13';
        $this->assertEquals(new DateTimeImmutable($date), $this->em->toAppData($date, new DateTimeModel()));
    }

    public function testInvalidDbDataException(): void
    {
        $this->expectException(InvalidDbDataException::class);
        $this->em->toAppData(true, new UintModel());
    }

    public function testNullDbDataNotAllowedException(): void
    {
        $this->expectException(NullDbDataNotAllowedException::class);
        $this->em->toAppData(null, new BoolModel());
    }
}