<?php

declare(strict_types=1);

namespace LMWF\Tests\Forms;

use LMWF\Constraint\Type\DataArrayModel;
use LMWF\ErrorHandling\ExceptionCode;
use LMWF\Form\Conf\FormConfFactory;
use PHPUnit\Framework\TestCase;

final class FormConfFactoryTest extends TestCase
{
    public function testWithUnsupportedModel(): void
    {
        $model = new DataArrayModel([
            'property' => new DataArrayModel([]),
        ]);
        $this->expectExceptionCode(ExceptionCode::FORM_CONF_FORMCONFFACTORY_MODEL_NOT_SUPPORTED->value);
        new FormConfFactory()->createConf($model, ['property' => ['label' => 'Hello']]);
    }
}
