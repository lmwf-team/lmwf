<?php

declare(strict_types=1);

namespace LM\WebFramework\Model\Type;

use LM\WebFramework\Model\Constraints\IEnumConstraint;
use LM\WebFramework\Model\Constraints\IRangeConstraint;
use LM\WebFramework\Model\Constraints\IRegexConstraint;
use LM\WebFramework\Model\Constraints\IUploadedImageConstraint;
use LM\WebFramework\Model\Constraints\RangeConstraint;
use LM\WebFramework\Model\Constraints\RegexConstraint;

final class StringModel extends AbstractModel implements IScalarModel
{
    private ?IEnumConstraint $enumConstraint;

    private ?IUploadedImageConstraint $uploadedImageConstraint;

    private ?RangeConstraint $rangeConstraint;

    private ?RegexConstraint $regexConstraint;

    /**
     * @param \LM\WebFramework\Model\Constraints\IConstraint[] $constraints
     */
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

    public function getEnumConstraint(): IEnumConstraint
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