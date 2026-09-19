<?php

declare(strict_types=1);

namespace LMWF\Form\Conf;

use LMWF\Constraint\Value\IRangeConstraint;
use LMWF\Constraint\Type\IScalarModel;
use LMWF\Constraint\Type\StringEnumModel;
use Traversable;
use LMWF\Form\Defaults\IDefaultCallable;

/**
 * Configuration for a form field accepting scalar data (data matched by a
 * IScalarModel), containing information to display the field and convert the
 * submitted data into app data.
 *
 * @phpstan-type fieldvalues iterable<array{text: string, value: int|string}>
 */
readonly class FormFieldConf
{
    /**
     * @template T
     * @param string $label The label to describe to the user the field.
     * @param ?IDefaultCallable<T> $default A function to call with the
     * parent submitted data to set the value of the field for this submission
     * in case no value was submitted.
     * @param FormFieldType $type The input type of the field.
     * @param null|fieldvalues $values All the values allowed for the field and
     * their associated label (hence why we cannot get it directly from model).
     * @param ?IRangeConstraint $rangeConstraint
     * @param ?StringEnumModel $stringEnumModel If not null, it means the field
     * should be attempted to be converted to a specific enum case if possible,
     * or otherwise default to a string.
     * @todo Use enum for type, with support for file and image to determine accept?
     * @todo For $values, create struct for items? (with keys 'value' and 'text' or 'label')
     */
    public function __construct(
        public string $label,
        public ?string $autocomplete,
        public ?IDefaultCallable $default,
        public ?string $id,
        public bool $isRequired,
        public ?IRangeConstraint $rangeConstraint,
        public FormFieldType $type,
        public null|array|Traversable $values,
        public ?StringEnumModel $stringEnumModel = null,
    ) {
    }
}
