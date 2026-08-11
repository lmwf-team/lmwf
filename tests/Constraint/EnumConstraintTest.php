<?php

declare(strict_types=1);

namespace LMWF\Tests\Constraint;

use LMWF\Constraint\Value\EnumConstraint;
use LMWF\Constraint\Value\IEnumConstraint;
use LMWF\Tests\Mocks\StringEnum;
use PHPUnit\Framework\TestCase;

final class EnumConstraintTest extends TestCase
{
    public function test(): void
    {
        $constraint = new EnumConstraint(StringEnum::cases());
        self::assertInstanceOf(IEnumConstraint::class, $constraint);
    }
}