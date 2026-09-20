<?php

declare(strict_types=1);

namespace LMWF\Form\Transformer;

use BackedEnum;
use LMWF\Constraint\Type\StringEnumModel;
use LMWF\Form\DataStructures\NonExistingEnumCase;
use Override;

final class StringEnumTransformer extends AbstractStringTransformer implements IFormTransformer
{
    public function __construct(
        private StringEnumModel $model,
        string $name,
    ) {
        parent::__construct($name);
    }

    #[\Override]
    public function transformSubmittedData(array $parsedPayload, array $uploadedFiles): null|BackedEnum|NonExistingEnumCase
    {
        $submittedStr = parent::extractTextInput($parsedPayload);
        if (null === $submittedStr) {
            return null;
        }
        foreach ($this->model->cases as $case) {
            if ($case->value === $submittedStr) {
                return $case;
            }
        }
        return new NonExistingEnumCase($submittedStr);
    }
}
