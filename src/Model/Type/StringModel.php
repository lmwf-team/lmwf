<?php

declare(strict_types=1);

namespace LM\WebFramework\Model\Type;

use LM\WebFramework\Model\Constraints\IRangeConstraint;
use LM\WebFramework\Model\Constraints\IRegexConstraint;
use LM\WebFramework\Model\Constraints\RangeConstraint;
use LM\WebFramework\Model\Constraints\RegexConstraint;

final class StringModel extends AbstractModel implements IScalarModel
{
    // new StringConstraint(minLength: 1, regex: StringConstraint::REGEX_DASHES),
    // 0, 255

    private ?RangeConstraint $rangeConstraint;

    private ?RegexConstraint $regexConstraint;

    /**
     * @param \LM\WebFramework\Model\Constraints\IConstraint[] $constraints
     */
    public function __construct(
        ?int $min = null,
        ?int $max = null,
        ?string $regex = null,
        bool $isNullable = false,
    ) {
        $this->rangeConstraint = (null === $max && null === $min) ? null : new RangeConstraint($min, $max);
        $this->regexConstraint = (null === $regex) ? null : new RegexConstraint($regex);
        parent::__construct($isNullable);
    }

    public function getRangeConstraint(): ?IRangeConstraint
    {
        return $this->rangeConstraint;
    }
    
    public function getRegexConstraint(): ?IRegexConstraint
    {
        return $this->regexConstraint;
    }
}