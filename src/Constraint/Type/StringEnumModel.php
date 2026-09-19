<?php

declare(strict_types=1);

namespace LMWF\Constraint\Type;

use BackedEnum;
use InvalidArgumentException;
use LMWF\ErrorHandling\ExceptionCode;
use Override;

final class StringEnumModel extends AbstractModel implements IScalarModel
{
    /**
     * @param list<BackedEnum<string>> $cases Set of allowed string-backed enum
     * cases.
     */
    public function __construct(
        public readonly array $cases,
        bool $isNullable = false,
    ) {
        foreach ($cases as $case) {
            if (!$case instanceof BackedEnum || !is_string($case->value)) {
                throw new InvalidArgumentException(code: ExceptionCode::CONSTRAINTS_TYPE_STRINGENUM_BAD_CASE->value);
            }
        }
        return parent::__construct($isNullable);
    }
}
