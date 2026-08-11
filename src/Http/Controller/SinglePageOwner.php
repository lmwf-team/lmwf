<?php

declare(strict_types=1);

namespace LMWF\Http\Controller;

use LMWF\DataStructures\Page;

/**
 * @todo Delete?
 */
interface SinglePageOwner
{
    public function getPage(): Page;
}
