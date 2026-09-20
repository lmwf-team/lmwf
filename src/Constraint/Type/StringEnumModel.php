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
     * @param list<BackedEnum> $cases Set of allowed string-backed enum
     * cases.
     * @todo ci_cd Tell Phpstan for support for generic in backed enums (e.g.
     * BackedEnum<string>).
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
        parent::__construct($isNullable);
    }
}
