<?php

declare(strict_types=1);

namespace LMWF\Form\Transformer;

use BackedEnum;
use LMWF\Constraint\Type\StringEnumModel;
use Override;

final class StringEnumTransformer extends AbstractStringTransformer implements IFormTransformer
{
    public function __construct(
        private StringEnumModel $model,
        string $name,
    ) {
        return parent::__construct($name);
    }

    #[\Override]
    public function transformSubmittedData(array $parsedPayload, array $uploadedFiles): null|string|BackedEnum
    {
        $submittedStr = parent::extractTextInput($parsedPayload);
        foreach ($this->model->cases as $case) {
            if ($case->value === $submittedStr) {
                return $case;
            }
        }
        return $submittedStr;
    }
}
