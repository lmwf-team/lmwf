<?php

declare(strict_types=1);

namespace LM\WebFramework\Form\Conf;

use IteratorAggregate;
use LM\WebFramework\Model\Type\IModel;

readonly class FormFieldConf
{
    /**
     * @todo Use enum for type, with support for file and image to determine accept?
     * @todo For $values, create struct for items? (with keys 'value' and 'text' or 'label')
     */
    public function __construct(
        public IModel $model,
        public string $label,
        public ?string $autocomplete,
        public ?bool $isRequired,
        public ?string $type,
        public ?IteratorAggregate $values,
    ) {
    }
}