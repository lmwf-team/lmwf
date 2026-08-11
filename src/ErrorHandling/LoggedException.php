<?php

declare(strict_types=1);

namespace LM\WebFramework\ErrorHandling;

use Exception;
use LM\WebFramework\DataStructures\IArrayable;
use Throwable;

/**
 * @todo To delete. (Absolutely useless.)
 */
final class LoggedException extends Exception implements IArrayable
{
    public function __construct(
        string $message,
        int $code,
        protected string $file,
        protected int $line,
        private int $time,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    #[\Override]
    public function toArray(): array
    {
        $asArray = [
            'code' => $this->code,
            'message' => $this->message,
            'file' => $this->file,
            'line' => $this->line,
            'time' => $this->time,
            'trace' => $this->getTrace(),
        ];

        $previous = $this->getPrevious();
        if (null !== $previous) {
            $asArray['previous'] = $previous instanceof IArrayable ? $previous->toArray() : $previous->__toString();
        }

        return $asArray;
    }

    #[\Override]
    public function isEqual(mixed $value): bool
    {
        return $this === $value;
    }
}
