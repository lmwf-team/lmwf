<?php

declare(strict_types=1);

namespace LM\WebFramework\Tests\Validator;

use InvalidArgumentException;
use LM\WebFramework\Model\Constraints\RangeConstraint;
use LM\WebFramework\Model\Type\AbstractModel;
use LM\WebFramework\Model\Type\BoolModel;
use LM\WebFramework\Model\Type\StringModel;
use LM\WebFramework\Validator\ModelValidator;
use PHPUnit\Framework\TestCase;

final class ValidatorTest extends TestCase
{
    public function testNullable(): void
    {
        $this->assertEmpty((new ModelValidator(new BoolModel(true)))->validate(null), 'Null should be allowed.');
        $this->assertNotEmpty((new ModelValidator(new BoolModel()))->validate(null), 'Null should NOT be allowed.');
        $this->assertNotEmpty((new ModelValidator(new BoolModel()))->validate(null), 'Null should NOT be allowed.');
    }

    public function testUnsupportedModel(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ModelValidator(new class extends AbstractModel {});
    }
    
    public function testStringValidator(): void
    {
        $myString = 'Hello';
        $this->assertEmpty((new ModelValidator(new StringModel()))->validate($myString));
        $this->assertNotEmpty((new ModelValidator(new StringModel(7, 10)))->validate($myString));
        $this->assertEmpty((new ModelValidator(new StringModel(regex: '[a-zA-Z]+')))->validate($myString));
        $this->assertNotEmpty((new ModelValidator(new StringModel(regex: '[0-9]+')))->validate($myString));
        
        $this->expectException(InvalidArgumentException::class);
        new RangeConstraint(10, 5);
    }
}