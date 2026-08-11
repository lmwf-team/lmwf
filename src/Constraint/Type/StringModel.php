<?php

declare(strict_types=1);

namespace LM\WebFramework\Constraint\Type;

use LM\WebFramework\Constraint\Value\IEnumConstraint;
use LM\WebFramework\Constraint\Value\IRangeConstraint;
use LM\WebFramework\Constraint\Value\IRegexConstraint;
use LM\WebFramework\Constraint\Value\IUploadedImageConstraint;
use LM\WebFramework\Constraint\Value\RangeConstraint;
use LM\WebFramework\Constraint\Value\RegexConstraint;

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
