<?php

declare(strict_types=1);

namespace LM\WebFramework\Form\Conf;

use Closure;
use LM\WebFramework\Model\Constraints\RangeConstraint;
use LM\WebFramework\Model\Type\IModel;
use Traversable;

readonly class FormFieldConf
{
    /**
     * @param ?IModel $model The data model for the field's value.
     * @param string $label The label to describe to the user the field.
     * @param ?Closure $closure A function to call with the submitted data to
     * set the value of the field in case no value was submitted.
     * @param ?string $type The input type of the field.
     * @param null|array|Traversable All the values allowed for the field.
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
        public ?RangeConstraint $rangeConstraint,
        public FormFieldType $type,
        public null|array|Traversable $values,
    ) {
    }
}
