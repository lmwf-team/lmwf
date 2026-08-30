<?php

declare(strict_types=1);

namespace LMWF\ErrorHandling;

use Stringable;
use UnexpectedValueException;

abstract class BaseUnexpectedValueException extends UnexpectedValueException
{
    protected function getStringDesc(mixed $value): string
    {
        $valueInfos = $this->getInfosOnValue($value);

        return $valueInfos['wide_type'] .
            (null === $valueInfos['value'] ? '' : ' with value ' . $valueInfos['value']) .
            ' of type ' . $valueInfos['type'];
    }

    /**
     * @return array{wide_type: string, value: ?string, type: string}
     */
    protected function getInfosOnValue(mixed $value): array
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
