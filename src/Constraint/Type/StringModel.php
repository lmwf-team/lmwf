<?php

declare(strict_types=1);

namespace LMWF\Constraint\Type;

use LMWF\Constraint\Value\IEnumConstraint;
use LMWF\Constraint\Value\IRangeConstraint;
use LMWF\Constraint\Value\IRegexConstraint;
use LMWF\Constraint\Value\IUploadedImageConstraint;
use LMWF\Constraint\Value\RangeConstraint;
use LMWF\Constraint\Value\RegexConstraint;

final class StringModel extends AbstractModel implements ILengthModel, IScalarModel
{
    private ?IEnumConstraint $enumConstraint;

    private ?IUploadedImageConstraint $uploadedImageConstraint;

    private ?RangeConstraint $rangeConstraint;

    private ?RegexConstraint $regexConstraint;

    public function __construct(
        ?int $lowerLimit = null,
        ?int $upperLimit = null,
        ?string $regex = null,
        ?IEnumConstraint $enumConstraint = null,
        ?IUploadedImageConstraint $uploadedImageConstraint = null,
        bool $isNullable = false,
    ) {
        $this->rangeConstraint = (null === $lowerLimit && null === $upperLimit) ? null : new RangeConstraint($lowerLimit, $upperLimit);
        $this->regexConstraint = (null === $regex) ? null : new RegexConstraint($regex);
        $this->enumConstraint = $enumConstraint;
        $this->uploadedImageConstraint = $uploadedImageConstraint;
        parent::__construct($isNullable);
    }

    public function getEnumConstraint(): ?IEnumConstraint
    {
        return $this->enumConstraint;
    }

    #[\Override]
    public function getRangeConstraint(): ?IRangeConstraint
    {
        return $this->rangeConstraint;
    }

    public function getRegexConstraint(): ?IRegexConstraint
    {
        return $this->regexConstraint;
    }

    public function getUploadedImageConstraint(): ?IUploadedImageConstraint
    {
        return $this->uploadedImageConstraint;
    }
}
