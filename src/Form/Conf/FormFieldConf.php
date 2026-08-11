<?php

declare(strict_types=1);

namespace LM\WebFramework\Form\Conf;

use LM\WebFramework\Model\Type\IModel;

readonly class FormFieldConf
{
    public function __construct(
        public IModel $model,
        public string $label,
        public ?string $accept,
        public ?string $autocomplete,
        public ?bool $isRequired,
        public ?string $type,
    ) {
    }
}