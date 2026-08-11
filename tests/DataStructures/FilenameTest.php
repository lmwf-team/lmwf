<?php

declare(strict_types=1);

use LMWF\DataStructures\Filename;
use PHPUnit\Framework\TestCase;

final class FilenameTest extends TestCase
{
    public function testInvalidAccents(): void
    {
        self::expectException(InvalidArgumentException::class);
        new Filename('mon-fichié', 'txt');
    }

    public function testInvalidMisplacedSepBeginning(): void
    {
        self::expectException(InvalidArgumentException::class);
        new Filename('-myfile', 'txt');
    }

    public function testInvalidMisplacedSepEnd(): void
    {
        self::expectException(InvalidArgumentException::class);
        new Filename('myfile-', 'txt');
    }

    public function testInvalidSepInExt(): void
    {
        self::expectException(InvalidArgumentException::class);
        new Filename('myfile', 't-xt');
    }

    public function testInvalidUnderscore(): void
    {
        self::expectException(InvalidArgumentException::class);
        new Filename('my_file', 'txt');
    }

    public function testValid(): void
    {
        $filename = new Filename('my-file', 'txt');
        self::assertEquals($filename->getFilename(), 'my-file.txt');
    }

    public function testWithExt(): void
    {
        self::assertEquals('my-file.ext', new Filename('my-file', 'png')->withExt('ext'));
    }
}