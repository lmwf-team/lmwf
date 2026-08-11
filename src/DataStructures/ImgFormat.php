<?php

declare(strict_types=1);

namespace LMWF\DataStructures;

final readonly class ImgFormat
{
    /**
     * @var int<1, 100>
     */
    const WEBP_QUALITY_HIGH = 95;

    /**
     * @param positive-int $minSizeX
     * @param positive-int $minSizeY
     * @param int<1, 100> $webpQuality
     */
    public function __construct(
        public int $minSizeX,
        public int $minSizeY,
        public int $webpQuality,
    ) {
    }

    /**
     * @param positive-int $sizeX
     * @param positive-int $sizeY
     * @return array{0: positive-int, 1: positive-int}
     */
    public function scale(int $sizeX, int $sizeY): array
    {
        $scaleFactor = max($this->minSizeX / $sizeX, $this->minSizeY / $sizeY);
        
        $newSizeX = (int) max(1, round($sizeX * $scaleFactor));
        $newSizeY = (int) max(1, round($sizeY * $scaleFactor));
        
        return [
            $newSizeX >= 1 ? $newSizeX : 1,
            $newSizeY >= 1 ? $newSizeY : 1,
        ];
    }
}