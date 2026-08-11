<?php

declare(strict_types=1);

namespace LM\WebFramework\Tests;

use DateTimeImmutable;
use LM\WebFramework\Database\DbEntityManager;
use LM\WebFramework\Database\Exceptions\InvalidDbDataException;
use LM\WebFramework\Database\Exceptions\NullDbDataNotAllowedException;
use LM\WebFramework\Model\BoolModel;
use LM\WebFramework\Model\DateTimeModel;
use LM\WebFramework\Model\IntegerModel;
use LM\WebFramework\Model\ListModel;
use LM\WebFramework\Model\UintModel;
use PHPUnit\Framework\TestCase;

final class DbEntityManagerTest extends TestCase
{
    private DbEntityManager $em;

    public function setUp(): void
    {
        $this->em = new DbEntityManager();
    }

    public function testScalarTypes(): void
    {
        

        $this->assertTrue($this->em->convertDbScalar(1, new BoolModel()));
        $this->assertFalse($this->em->convertDbScalar(0, new BoolModel()));
        $this->assertNull($this->em->convertDbScalar(null, new BoolModel(isNullable: true)));

        $this->assertEquals(47, $this->em->convertDbScalar(47, new IntegerModel()));

        $date = '2024-07-13';
        $this->assertEquals(new DateTimeImmutable($date), $this->em->convertDbScalar($date, new DateTimeModel()));
    }

    public function testInvalidDbDataException(): void
    {
        $this->expectException(InvalidDbDataException::class);
        $this->em->convertDbScalar(true, new UintModel());
    }

    public function testNullDbDataNotAllowedException(): void
    {
        $this->expectException(NullDbDataNotAllowedException::class);
        $this->em->convertDbScalar(null, new BoolModel());
    }

    // public function testListModel(): void
    // {
    //     $toAppDatad = $this->em->convertDbScalar(['1', '2', '3'], new ListModel(new UintModel()));
    //     $this->assertEquals([1, 2, 3], $toAppDatad);
    // }
}