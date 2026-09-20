<?php

declare(strict_types=1);

namespace LMWF\Http\Factory;

use LMWF\Http\Routing\FormatErr;

final readonly class PageEntTitleErr
{
    public function __construct(
        public FormatErr $formatErr,
    ) {
    }
}
