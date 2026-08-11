<?php

declare(strict_types=1);

namespace LM\WebFramework\Form\Transformer;

use LM\WebFramework\Form\Exceptions\MissingInputException;
use UnexpectedValueException;

abstract class AbstractStringTransformer
{
    public function __construct(
        private string $name,
    ) {
    }

    /**
     * @return string|null The submitted, non-empty string, or null if the string is empty.
     * @throws MissingInputException If no input bears the specified name.
     * @throws UnexpectedValueException If the value associated with the input is not a string.
     */
    public function extractTextInput(array $parsedPayload): ?string
    {
        if (!key_exists($this->name, $parsedPayload)) {
            throw new MissingInputException($this->name);
        }
        $value = $parsedPayload[$this->name];
        if (!is_string($value)) {
            throw new UnexpectedValueException('Submitted value is expected to be string.');
        }

        return '' !== $value ? $value : null;
    }
}
