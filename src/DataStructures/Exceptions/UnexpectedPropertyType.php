<?php

declare(strict_types=1);

namespace LMWF\DataStructures\Exceptions;

use LMWF\ErrorHandling\ExceptionCode;
use Stringable;
use Throwable;
use UnexpectedValueException;

final class UnexpectedPropertyType extends UnexpectedValueException
{
    public function __construct(int|string $key, string $expectedType, mixed $actualValue, Throwable|null $previous = null)
    {
        $actualInfos = $this->getDisplayedActual($actualValue);

        $displayedActual = $actualInfos['wide_type'] .
            (null === $actualInfos['value'] ? '' : ' with value ' . $actualInfos['value']) .
            ' of type ' . $actualInfos['type'];

        parent::__construct(
            "Property with key '$key' does not have the expected type '$expectedType', got $displayedActual instead.",
            ExceptionCode::APP_TRAVERSABLE_UNEXPECTED_PROPERTY_TYPE->value,
            $previous,
        );
    }

    /**
     * @return array{wide_type: string, value: ?string, type: string}
     */
    private function getDisplayedActual(mixed $value): array
    {
        if (is_object($value)) {
            if ($value instanceof Stringable) {
                return [
                    'wide_type' => 'object',
                    'value' => $value->__toString(),
                    'type' => get_class($value),
                ];
            }
            return [
                'wide_type' => 'object',
                'value' => null,
                'type' => get_class($value),
            ];
        } elseif (is_bool($value)) {
            return [
                'wide_type' => 'scalar',
                'value' => $value ? 'true' : 'false',
                'type' => gettype($value),
            ];
        } elseif (is_scalar($value)) {
            return [
                'wide_type' => 'scalar',
                'value' => (string) $value,
                'type' => gettype($value),
            ];
        } elseif (null === $value) {
            return [
                'wide_type' => 'null',
                'value' => null,
                'type' => 'null',
            ];
        }
        return [
            'wide_type' => 'mixed',
            'value' => null,
            'type' => gettype($value),
        ];
    }
}
