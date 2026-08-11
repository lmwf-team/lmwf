<?php

declare(strict_types=1);

namespace LM\WebFramework\Test;

interface IUnitTest
{
    public function run(): array;
}