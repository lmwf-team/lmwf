<?php

declare(strict_types=1);

namespace LMWF\Form\Conf;

use Closure;
use LMWF\Constraint\Value\IRangeConstraint;
use LMWF\Constraint\Type\IModel;
use Traversable;

readonly class FormFieldConf
{
    /**
     * @param ?IModel $model The data model for the field's value.
     * @param string $label The label to describe to the user the field.
     * @param ?Closure $default A function to call with the submitted data to
     * set the value of the field in case no value was submitted.
     * @param FormFieldType $type The input type of the field.
     * @param null|iterable<array{text: string, value: int|string}> $values All the values allowed for the field.
     * @todo Use enum for type, with support for file and image to determine accept?
     * @todo For $values, create struct for items? (with keys 'value' and 'text' or 'label')
     */
    public function __construct(
        public ?IModel $model,
        public string $label,
        public ?string $autocomplete,
        public ?Closure $default,
        public ?string $id,
        public bool $isRequired,
        public ?IRangeConstraint $rangeConstraint,
        public FormFieldType $type,
        public null|array|Traversable $values,
    ) {
    }
}
